<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\Action;

use Oro\Bundle\AuthorizeNetBundle\Action\PaymentTransactionVerifyAction;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Response\ResponseInterface;
use Oro\Bundle\AuthorizeNetBundle\Method\AuthorizeNetPaymentMethod;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;
use Oro\Bundle\PaymentBundle\Method\PaymentMethodInterface;
use Oro\Bundle\PaymentBundle\Method\Provider\PaymentMethodProviderInterface;
use Oro\Bundle\PaymentBundle\Provider\PaymentTransactionProvider;
use Oro\Component\ConfigExpression\ContextAccessor;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\PropertyAccess\PropertyPath;
use Symfony\Component\Routing\RouterInterface;

class PaymentTransactionVerifyActionTest extends \PHPUnit\Framework\TestCase
{
    public const PAYMENT_METHOD = 'authorize_net_4';

    /** @var ContextAccessor|\PHPUnit\Framework\MockObject\MockObject */
    private $contextAccessor;

    /** @var PaymentMethodProviderInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $paymentMethodProvider;

    /** @var PaymentTransactionProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $paymentTransactionProvider;

    /** @var RouterInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $router;

    /** @var EventDispatcherInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $dispatcher;

    /** @var LoggerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $logger;

    /** @var PaymentTransactionVerifyAction */
    private $action;

    #[\Override]
    protected function setUp(): void
    {
        $this->contextAccessor = $this->createMock(ContextAccessor::class);
        $this->paymentMethodProvider = $this->createMock(PaymentMethodProviderInterface::class);
        $this->paymentTransactionProvider = $this->createMock(PaymentTransactionProvider::class);
        $this->router = $this->createMock(RouterInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);

        $this->action = new PaymentTransactionVerifyAction(
            $this->contextAccessor,
            $this->paymentMethodProvider,
            $this->paymentTransactionProvider,
            $this->router
        );
        $this->action->setLogger($this->logger);
        $this->action->setDispatcher($this->dispatcher);
    }

    public function testExecuteFailedAction(): void
    {
        $context = [];
        $transaction = (new PaymentTransaction())
            ->setPaymentMethod(self::PAYMENT_METHOD)
            ->setAction(AuthorizeNetPaymentMethod::VERIFY);

        $options = [
            'attribute' => new PropertyPath('result'),
            'transactionOptions' => [],
            'object' => $transaction,
            'paymentMethod' => self::PAYMENT_METHOD
        ];

        $this->contextAccessor->expects(self::any())
            ->method('getValue')
            ->willReturnArgument(1);

        $this->action->initialize($options);

        $paymentMethod = $this->createMock(AuthorizeNetPaymentMethod::class);
        $paymentMethod->expects(self::once())
            ->method('execute')
            ->with($transaction->getAction(), $transaction)
            ->willReturn(['successful' => false]);

        $this->paymentMethodProvider->expects(self::atLeastOnce())
            ->method('hasPaymentMethod')
            ->with($options['paymentMethod'])
            ->willReturn(true);

        $this->paymentMethodProvider->expects(self::atLeastOnce())
            ->method('getPaymentMethod')
            ->with($options['paymentMethod'])
            ->willReturn($paymentMethod);

        $data = [
            'successful' => false,
            'message' => 'oro.payment.message.error'
        ];
        $this->contextAccessor->expects(self::once())
            ->method('setValue')
            ->with($context, $options['attribute'], $data);

        $this->action->execute($context);
    }

    /**
     * @dataProvider executeDataProvider
     */
    public function testExecuteAction(array $response): void
    {
        $context = [];
        $transaction = (new PaymentTransaction())
            ->setPaymentMethod(self::PAYMENT_METHOD)
            ->setAction(AuthorizeNetPaymentMethod::VERIFY);

        $options = [
            'attribute' => new PropertyPath('result'),
            'transactionOptions' => [],
            'object' => $transaction,
            'paymentMethod' => self::PAYMENT_METHOD
        ];

        $this->contextAccessor->expects(self::any())
            ->method('getValue')
            ->willReturnArgument(1);

        $this->action->initialize($options);

        $paymentMethod = $this->createMock(AuthorizeNetPaymentMethod::class);
        $paymentMethod->expects(self::once())
            ->method('execute')
            ->with($transaction->getAction(), $transaction)
            ->willReturn($response);

        $this->paymentMethodProvider->expects(self::atLeastOnce())
            ->method('hasPaymentMethod')
            ->with($options['paymentMethod'])
            ->willReturn(true);

        $this->paymentMethodProvider->expects(self::atLeastOnce())
            ->method('getPaymentMethod')
            ->with($options['paymentMethod'])
            ->willReturn($paymentMethod);

        $data = [
            'successful' => $response['received_successful'],
            'message' => $response['message']
        ];
        $this->contextAccessor->expects(self::once())
            ->method('setValue')
            ->with($context, $options['attribute'], $data);

        $this->paymentTransactionProvider->expects(self::any())
            ->method('savePaymentTransaction')
            ->with($transaction);

        $this->action->execute($context);

        self::assertEquals($transaction->getAction(), $response['action']);
    }

    public function executeDataProvider(): array
    {
        return [
            'not approved' => [
                'response' => [
                    'successful' => true,
                    'received_successful' => true,
                    'transaction' => [
                        'response_code' => ResponseInterface::TRANS_NOT_APPROVED_RESPONSE_CODE,
                        'transaction_status' => PaymentTransactionVerifyAction::FDS_PENDING_REVIEW
                    ],
                    'message' => 'oro.authorize_net.message.not_approved',
                    'action' => AuthorizeNetPaymentMethod::VERIFY
                ],
            ],
            'not approved with authorize' => [
                'response' => [
                    'successful' => true,
                    'received_successful' => true,
                    'transaction' => [
                        'response_code' => ResponseInterface::TRANS_NOT_APPROVED_RESPONSE_CODE,
                        'transaction_status' => PaymentTransactionVerifyAction::FDS_AUTHORIZED_PENDING_REVIEW
                    ],
                    'message' => 'oro.authorize_net.message.not_approved',
                    'action' => AuthorizeNetPaymentMethod::VERIFY
                ],
            ],
            'pending capture' => [
                'response' => [
                    'successful' => true,
                    'received_successful' => true,
                    'transaction' => [
                        'response_code' => ResponseInterface::TRANS_SUCCESSFUL_RESPONSE_CODE,
                        'transaction_status' => PaymentTransactionVerifyAction::AUTHORIZED_PENDING_CAPTURE
                    ],
                    'message' => 'oro.authorize_net.message.approved',
                    'action' => PaymentMethodInterface::AUTHORIZE
                ],
            ],
            'already charged' => [
                'response' => [
                    'successful' => true,
                    'received_successful' => true,
                    'transaction' => [
                        'response_code' => ResponseInterface::TRANS_SUCCESSFUL_RESPONSE_CODE,
                        'transaction_status' => PaymentTransactionVerifyAction::CAPTURE_PENDING_SETTLEMENT
                    ],
                    'message' => 'oro.authorize_net.message.charged',
                    'action' => PaymentMethodInterface::CHARGE
                ],
            ],
            'voided' => [
                'response' => [
                    'successful' => true,
                    'received_successful' => true,
                    'transaction' => [
                        'response_code' => ResponseInterface::TRANS_SUCCESSFUL_RESPONSE_CODE,
                        'transaction_status' => PaymentTransactionVerifyAction::VOIDED
                    ],
                    'message' => 'oro.authorize_net.message.voided',
                    'action' => 'voided'
                ],
            ],
            'declined' => [
                'response' => [
                    'successful' => true,
                    'received_successful' => true,
                    'transaction' => [
                        'response_code' => '3',
                        'transaction_status' => PaymentTransactionVerifyAction::DECLINED
                    ],
                    'message' => 'oro.authorize_net.message.declined',
                    'action' => 'declined'
                ],
            ],
            'error status' => [
                'response' => [
                    'successful' => true,
                    'received_successful' => false,
                    'transaction' => [
                        'response_code' => ResponseInterface::TRANS_SUCCESSFUL_RESPONSE_CODE,
                        'transaction_status' => 'error'
                    ],
                    'message' => 'oro.payment.message.error',
                    'action' => AuthorizeNetPaymentMethod::VERIFY
                ]
            ]
        ];
    }
}
