<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\Method\View;

use Oro\Bundle\AuthorizeNetBundle\Form\Type\BankAccountType;
use Oro\Bundle\AuthorizeNetBundle\Form\Type\CheckoutEcheckProfileType;
use Oro\Bundle\AuthorizeNetBundle\Method\Config\AuthorizeNetConfigInterface;
use Oro\Bundle\AuthorizeNetBundle\Method\View\AuthorizeNetEcheckPaymentMethodView;
use Oro\Bundle\PaymentBundle\Context\PaymentContextInterface;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Component\Testing\Unit\EntityTrait;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class AuthorizeNetEcheckPaymentMethodViewTest extends \PHPUnit\Framework\TestCase
{
    use EntityTrait;

    /** @var FormFactoryInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $formFactory;

    /** @var AuthorizeNetEcheckPaymentMethodView */
    protected $methodView;

    /** @var AuthorizeNetConfigInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $paymentConfig;

    /** @var TokenAccessorInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $tokenAccessor;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->formFactory = $this->createMock(FormFactoryInterface::class);
        $this->paymentConfig = $this->createMock(AuthorizeNetConfigInterface::class);
        $this->tokenAccessor = $this->createMock(TokenAccessorInterface::class);

        $this->methodView = new AuthorizeNetEcheckPaymentMethodView(
            $this->formFactory,
            $this->tokenAccessor,
            $this->paymentConfig
        );
    }

    /**
     * @param bool $isEnabledCIM
     * @dataProvider getOptionsProvider
     */
    public function testGetOptions($isEnabledCIM)
    {
        [$formView, $context] = $this->prepareMocks($isEnabledCIM);

        $this->assertEquals(
            [
                'formView' => $formView,
                'paymentMethodComponentOptions' => [
                    'clientKey' => 'client key',
                    'apiLoginID' => 'api login id',
                    'testMode' => true,
                ],
            ],
            $this->methodView->getOptions($context)
        );
    }

    public function testGetPaymentMethodIdentifier()
    {
        $this->paymentConfig->expects($this->once())
            ->method('getPaymentMethodIdentifier')
            ->willReturn('identifier');

        $this->assertEquals('identifier', $this->methodView->getPaymentMethodIdentifier());
    }

    public function testGetLabel()
    {
        $this->paymentConfig->expects($this->once())
            ->method('getLabel')
            ->willReturn('label');

        $this->assertEquals('label', $this->methodView->getLabel());
    }

    public function testGetAdminLabel()
    {
        $this->paymentConfig->expects($this->once())
            ->method('getAdminLabel')
            ->willReturn('label');

        $this->assertEquals('label', $this->methodView->getAdminLabel());
    }

    public function testGetShortLabel()
    {
        $this->paymentConfig->expects($this->once())
            ->method('getShortLabel')
            ->willReturn('short label');

        $this->assertEquals('short label', $this->methodView->getShortLabel());
    }

    public function testGetBlock()
    {
        $this->assertEquals('_payment_methods_authorize_net_widget', $this->methodView->getBlock());
    }

    /**
     * @param bool $isEnabledCIM
     * @return array|\PHPUnit\Framework\MockObject\MockObject[]
     */
    protected function prepareMocks($isEnabledCIM)
    {
        $formView = $this->createMock(FormView::class);
        $form = $this->createMock(FormInterface::class);
        $form->expects($this->once())->method('createView')->willReturn($formView);

        $this->tokenAccessor
            ->expects($this->once())
            ->method('hasUser')
            ->willReturn(true);

        $formClass = BankAccountType::class;

        if ($isEnabledCIM) {
            $formClass = CheckoutEcheckProfileType::class;
        }

        $this->formFactory->expects($this->once())
            ->method('create')
            ->with($formClass, null, [
                'confirmation_text' => 'txt',
                'allowed_account_types' => ['test']
            ])
            ->willReturn($form);

        $this->paymentConfig->expects($this->once())
            ->method('getApiLoginId')
            ->willReturn('api login id');

        $this->paymentConfig->expects($this->once())
            ->method('getClientKey')
            ->willReturn('client key');

        $this->paymentConfig->expects($this->once())
            ->method('isTestMode')
            ->willReturn(true);

        $this->paymentConfig->expects($this->once())
            ->method('isEnabledCIM')
            ->willReturn($isEnabledCIM);

        $this->paymentConfig->expects($this->once())
            ->method('getECheckConfirmationText')
            ->willReturn('txt');

        $this->paymentConfig->expects($this->once())
            ->method('getECheckAccountTypes')
            ->willReturn(['test']);

        /** @var PaymentContextInterface|\PHPUnit\Framework\MockObject\MockObject $context */
        $context = $this->createMock(PaymentContextInterface::class);

        return array($formView, $context);
    }

    /**
     * @return array
     */
    public function getOptionsProvider()
    {
        return [
            'cim disabled' => [
                'isEnabledCIM' => false
            ],
            'cim enabled' => [
                'isEnabledCIM' => true
            ]
        ];
    }
}
