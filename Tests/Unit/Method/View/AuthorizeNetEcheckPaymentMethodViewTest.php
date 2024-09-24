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
    private $formFactory;

    /** @var AuthorizeNetEcheckPaymentMethodView */
    private $methodView;

    /** @var AuthorizeNetConfigInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $paymentConfig;

    /** @var TokenAccessorInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $tokenAccessor;

    #[\Override]
    protected function setUp(): void
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
     * @dataProvider getOptionsProvider
     */
    public function testGetOptions(bool $isEnabledCIM)
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

    private function prepareMocks(bool $isEnabledCIM): array
    {
        $formView = $this->createMock(FormView::class);
        $form = $this->createMock(FormInterface::class);
        $form->expects($this->once())
            ->method('createView')
            ->willReturn($formView);

        $this->tokenAccessor->expects($this->once())
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

        $context = $this->createMock(PaymentContextInterface::class);

        return [$formView, $context];
    }

    public function getOptionsProvider(): array
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
