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
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Contracts\Translation\TranslatorInterface;

class FraudDetectionTransactionListenerTest extends \PHPUnit\Framework\TestCase
{
    public const PAYMENT_METHOD = 'authorize_net_4';

    /** @var FraudDetectionTransactionListener */
    private $eventListener;

    /** @var AuthorizeNetConfigProviderInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $configProvider;

    /** @var Session|\PHPUnit\Framework\MockObject\MockObject */
    private $requestStack;

    /** @var TranslatorInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $translator;

    /** @var ResponseInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $response;

    /** @var AuthorizeNetConfigInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $paymentConfig;

    #[\Override]
    protected function setUp(): void
    {
        $this->configProvider = $this->createMock(AuthorizeNetConfigProviderInterface::class);
        $this->requestStack = $this->createMock(RequestStack::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->response = $this->createMock(AuthorizeNetSDKTransactionResponse::class);
        $this->paymentConfig = $this->createMock(AuthorizeNetConfigInterface::class);

        $this->eventListener = new FraudDetectionTransactionListener(
            $this->configProvider,
            $this->requestStack,
            $this->translator
        );
    }

    /**
     * @dataProvider getOptionsProvider
     */
    public function testOnTransactionResponseReceived(
        string $responseCode,
        bool $holdTransaction,
        string $action
    ): void {
        $transaction = (new PaymentTransaction())
            ->setPaymentMethod(self::PAYMENT_METHOD)
            ->setAction(PaymentMethodInterface::AUTHORIZE);

        $this->paymentConfig->expects($this->any())
            ->method('isAllowHoldTransaction')
            ->willReturn($holdTransaction);
        $this->configProvider->expects($this->any())
            ->method('getPaymentConfig')
            ->willReturn($this->paymentConfig);

        $this->response->expects($this->once())
            ->method('getData')
            ->willReturn([
                'transaction_response' => ['response_code' => $responseCode]
            ]);

        $event = new TransactionResponseReceivedEvent($this->response, $transaction);
        $this->eventListener->onTransactionResponseReceived($event);

        $this->assertEquals($transaction->getAction(), $action);
    }

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

        $this->paymentConfig->expects($this->any())
            ->method('isAllowHoldTransaction')
            ->willReturn(false);
        $this->configProvider->expects($this->once())
            ->method('getPaymentConfig')
            ->willReturn($this->paymentConfig);

        $this->response->expects($this->once())
            ->method('getData')
            ->willReturn([
                'transaction_response' => ['response_code' => '4']
            ]);

        $message = 'error_message';
        $this->translator->expects($this->once())
            ->method('trans')
            ->willReturn($message);

        $flashBag = $this->createMock(FlashBagInterface::class);
        $flashBag->expects($this->once())
            ->method('add')
            ->with('warning', $message);
        $sessionMock = $this->createMock(Session::class);
        $this->requestStack->expects($this->once())
            ->method('getSession')
            ->willReturn($sessionMock);
        $sessionMock->expects($this->once())
            ->method('getFlashBag')
            ->willReturn($flashBag);

        $this->expectException(TransactionLimitReachedException::class);
        $this->expectExceptionMessage('The transaction limit reached.');

        $event = new TransactionResponseReceivedEvent($this->response, $transaction);
        $this->eventListener->onTransactionResponseReceived($event);

        $this->assertEquals($transaction->getAction(), PaymentMethodInterface::AUTHORIZE);
    }
}
