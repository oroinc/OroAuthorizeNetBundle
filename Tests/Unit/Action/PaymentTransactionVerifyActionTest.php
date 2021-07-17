<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\Action;

use Oro\Bundle\AuthorizeNetBundle\Action\PaymentTransactionVerifyAction;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Response\ResponseInterface;
use Oro\Bundle\AuthorizeNetBundle\Method\AuthorizeNetPaymentMethod;
use Oro\Bundle\PaymentBundle\Action\AbstractPaymentMethodAction;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;
use Oro\Bundle\PaymentBundle\Method\PaymentMethodInterface;
use Oro\Bundle\PaymentBundle\Tests\Unit\Action\AbstractActionTest;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\PropertyAccess\PropertyPath;

class PaymentTransactionVerifyActionTest extends AbstractActionTest
{
    public const PAYMENT_METHOD = 'authorize_net_4';

    /**
     * {@inheritDoc}
     */
    protected function getAction(): AbstractPaymentMethodAction
    {
        return new PaymentTransactionVerifyAction(
            $this->contextAccessor,
            $this->paymentMethodProvider,
            $this->paymentTransactionProvider,
            $this->router
        );
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

        $this->contextAccessor
            ->expects(static::any())
            ->method('getValue')
            ->will($this->returnArgument(1));

        $this->action->initialize($options);

        /** @var AuthorizeNetPaymentMethod|MockObject $paymentMethod */
        $paymentMethod = $this->createMock(AuthorizeNetPaymentMethod::class);
        $paymentMethod->expects($this->once())
            ->method('execute')
            ->with($transaction->getAction(), $transaction)
            ->willReturn(['successful' => false]);

        $this->paymentMethodProvider
            ->expects($this->atLeastOnce())
            ->method('hasPaymentMethod')
            ->with($options['paymentMethod'])
            ->willReturn(true);

        $this->paymentMethodProvider
            ->expects($this->atLeastOnce())
            ->method('getPaymentMethod')
            ->with($options['paymentMethod'])
            ->willReturn($paymentMethod);

        $data = [
            'successful' => false,
            'message' => 'oro.payment.message.error'
        ];
        $this->contextAccessor
            ->expects($this->once())
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

        $this->contextAccessor
            ->expects($this->any())
            ->method('getValue')
            ->willReturnArgument(1);

        $this->action->initialize($options);

        /** @var AuthorizeNetPaymentMethod|MockObject $paymentMethod */
        $paymentMethod = $this->createMock(AuthorizeNetPaymentMethod::class);
        $paymentMethod->expects($this->once())
            ->method('execute')
            ->with($transaction->getAction(), $transaction)
            ->willReturn($response);

        $this->paymentMethodProvider
            ->expects($this->atLeastOnce())
            ->method('hasPaymentMethod')
            ->with($options['paymentMethod'])
            ->willReturn(true);

        $this->paymentMethodProvider
            ->expects($this->atLeastOnce())
            ->method('getPaymentMethod')
            ->with($options['paymentMethod'])
            ->willReturn($paymentMethod);

        $data = [
            'successful' => $response['received_successful'],
            'message' => $response['message']
        ];
        $this->contextAccessor
            ->expects($this->once())
            ->method('setValue')
            ->with($context, $options['attribute'], $data);

        $this->paymentTransactionProvider
            ->expects($this->any())
            ->method('savePaymentTransaction')
            ->with($transaction);

        $this->action->execute($context);

        $this->assertEquals($transaction->getAction(), $response['action']);
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
