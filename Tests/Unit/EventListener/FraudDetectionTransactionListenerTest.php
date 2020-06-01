<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\EventListener;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Response\AuthorizeNetSDKTransactionResponse;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Response\ResponseInterface;
use Oro\Bundle\AuthorizeNetBundle\Event\TransactionResponseReceivedEvent;
use Oro\Bundle\AuthorizeNetBundle\EventListener\FraudDetectionTransactionListener;
use Oro\Bundle\AuthorizeNetBundle\Exception\TransactionLimitReachedException;
use Oro\Bundle\AuthorizeNetBundle\Method\AuthorizeNetPaymentMethod;
use Oro\Bundle\AuthorizeNetBundle\Method\Config\AuthorizeNetConfigInterface;
use Oro\Bundle\AuthorizeNetBundle\Method\Config\Provider\AuthorizeNetConfigProviderInterface;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;
use Oro\Bundle\PaymentBundle\Method\PaymentMethodInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Contracts\Translation\TranslatorInterface;

class FraudDetectionTransactionListenerTest extends TestCase
{
    public const PAYMENT_METHOD = 'authorize_net_4';

    /**
     * @var FraudDetectionTransactionListener
     */
    private $eventListener;

    /**
     * @var AuthorizeNetConfigProviderInterface|MockObject
     */
    private $configProvider;

    /**
     * @var Session|MockObject
     */
    private $session;

    /**
     * @var TranslatorInterface|MockObject
     */
    private $translator;

    /**
     * @var ResponseInterface|MockObject
     */
    private $response;

    /**
     * @var AuthorizeNetConfigInterface|MockObject
     */
    private $paymentConfig;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->configProvider = $this->createMock(AuthorizeNetConfigProviderInterface::class);
        $this->session = $this->createMock(Session::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->response = $this->createMock(AuthorizeNetSDKTransactionResponse::class);
        $this->paymentConfig = $this->createMock(AuthorizeNetConfigInterface::class);

        $this->eventListener = new FraudDetectionTransactionListener(
            $this->configProvider,
            $this->session,
            $this->translator
        );
    }

    /**
     * @dataProvider getOptionsProvider
     * @param string $responseCode
     * @param bool $holdTransaction
     * @param string $action
     */
    public function testOnTransactionResponseReceived(
        string $responseCode,
        bool $holdTransaction,
        string $action
    ): void {
        $transaction = (new PaymentTransaction())
            ->setPaymentMethod(self::PAYMENT_METHOD)
            ->setAction(PaymentMethodInterface::AUTHORIZE);

        $this->paymentConfig
            ->expects($this->any())
            ->method('isAllowHoldTransaction')
            ->willReturn($holdTransaction);
        $this->configProvider
            ->expects($this->any())
            ->method('getPaymentConfig')
            ->willReturn($this->paymentConfig);

        $this->response
            ->expects($this->once())
            ->method('getData')
            ->willReturn([
                'transaction_response' => ['response_code' => $responseCode]
            ]);

        $event = new TransactionResponseReceivedEvent($this->response, $transaction);
        $this->eventListener->onTransactionResponseReceived($event);

        $this->assertEquals($transaction->getAction(), $action);
    }

    /**
     * @return array
     */
    public function getOptionsProvider(): array
    {
        return [
            'set verify action' => [
                'responseCode' => '4',
                'holdTransaction' => true,
                'action' => AuthorizeNetPaymentMethod::VERIFY
            ],
            'successful status' => [
                'responseCode' => '1',
                'holdTransaction' => true,
                'action' => PaymentMethodInterface::AUTHORIZE
            ],
        ];
    }

    public function testWithDisableHoldTransactionOption(): void
    {
        $transaction = (new PaymentTransaction())
            ->setPaymentMethod(self::PAYMENT_METHOD)
            ->setAction(PaymentMethodInterface::AUTHORIZE);

        $this->paymentConfig
            ->expects($this->any())
            ->method('isAllowHoldTransaction')
            ->willReturn(false);
        $this->configProvider
            ->expects($this->once())
            ->method('getPaymentConfig')
            ->willReturn($this->paymentConfig);

        $this->response
            ->expects($this->once())
            ->method('getData')
            ->willReturn([
                'transaction_response' => ['response_code' => '4']
            ]);

        $message = 'error_message';
        $this->translator
            ->expects($this->once())
            ->method('trans')
            ->willReturn($message);

        $flashBag = $this->createMock(FlashBagInterface::class);
        $flashBag
            ->expects($this->once())
            ->method('add')
            ->with('warning', $message);

        $this->session
            ->expects($this->once())
            ->method('getFlashBag')
            ->willReturn($flashBag);

        $this->expectException(TransactionLimitReachedException::class);
        $this->expectExceptionMessage('The transaction limit reached.');

        $event = new TransactionResponseReceivedEvent($this->response, $transaction);
        $this->eventListener->onTransactionResponseReceived($event);

        $this->assertEquals($transaction->getAction(), PaymentMethodInterface::AUTHORIZE);
    }
}
