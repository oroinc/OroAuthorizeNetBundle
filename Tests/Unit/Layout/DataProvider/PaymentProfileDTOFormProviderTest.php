<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\Layout\DataProvider;

use Oro\Bundle\AuthorizeNetBundle\Entity\CustomerPaymentProfile;
use Oro\Bundle\AuthorizeNetBundle\Form\Type\PaymentProfileDTOType;
use Oro\Bundle\AuthorizeNetBundle\Layout\DataProvider\PaymentProfileDTOFormProvider;
use Oro\Bundle\AuthorizeNetBundle\Method\Config\AuthorizeNetConfig;
use Oro\Bundle\AuthorizeNetBundle\Model\DTO\PaymentProfileDTO;
use Oro\Bundle\AuthorizeNetBundle\Provider\CIMEnabledIntegrationConfigProvider;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Oro\Component\Testing\Unit\EntityTrait;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PaymentProfileDTOFormProviderTest extends \PHPUnit\Framework\TestCase
{
    use EntityTrait;

    /** @var PaymentProfileDTOFormProvider */
    private $provider;

    /** @var FormFactoryInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $formFactory;

    /** @var \PHPUnit\Framework\MockObject\MockObject|UrlGeneratorInterface */
    private $router;

    /** @var \PHPUnit\Framework\MockObject\MockObject|CIMEnabledIntegrationConfigProvider */
    private $configProvider;

    /** @var \PHPUnit\Framework\MockObject\MockObject|WebsiteManager */
    private $websiteManager;

    /** @var array */
    private static $config = [
        AuthorizeNetConfig::API_LOGIN_ID => 'login',
        AuthorizeNetConfig::CLIENT_KEY => 'key',
        AuthorizeNetConfig::TEST_MODE_KEY => true,
        AuthorizeNetConfig::ALLOWED_CREDIT_CARD_TYPES_KEY => ['visa'],
        AuthorizeNetConfig::REQUIRE_CVV_ENTRY_KEY => true,
        AuthorizeNetConfig::ECHECK_ACCOUNT_TYPES => ['test']
    ];

    protected function setUp(): void
    {
        $this->formFactory = $this->createMock(FormFactoryInterface::class);
        $this->router = $this->createMock(UrlGeneratorInterface::class);

        $this->configProvider = $this->createMock(CIMEnabledIntegrationConfigProvider::class);

        $this->provider = new PaymentProfileDTOFormProvider(
            $this->formFactory,
            $this->router,
            $this->configProvider
        );
    }

    /**
     * @dataProvider paymentProfileDTOProvider
     */
    public function testGetPaymentProfileDTOFormView(PaymentProfileDTO $paymentProfileDTO)
    {
        $this->configProvider->expects($this->any())
            ->method('getConfig')
            ->willReturn(new AuthorizeNetConfig(self::$config));

        $paymentProfile = $paymentProfileDTO->getProfile();
        $type = $paymentProfile->getType();
        $id = $paymentProfile->getId();
        $this->router->expects($this->any())
            ->method('generate')
            ->willReturnMap([
                [PaymentProfileDTOFormProvider::PAYMENT_PROFILE_CREATE_ROUTE_NAME, ['type' => $type], 1, 'create_link'],
                [PaymentProfileDTOFormProvider::PAYMENT_PROFILE_UPDATE_ROUTE_NAME, ['id' => $id], 1, 'update_link']
            ]);

        $config = new AuthorizeNetConfig(self::$config);
        $expectedOptions = [
            'action' => $id ? 'update_link' : 'create_link',
            'requireCvvEntryEnabled' => $config->isRequireCvvEntryEnabled(),
            'allowed_account_types' => $config->getECheckAccountTypes(),
            'paymentProfileComponentOptions' => [
                'allowedCreditCards' => $config->getAllowedCreditCards(),
                'clientKey' => $config->getClientKey(),
                'apiLoginID' => $config->getApiLoginId(),
                'testMode' => $config->isTestMode(),
            ]
        ];

        $formView = $this->createMock(FormView::class);

        $form = $this->createMock(FormInterface::class);
        $form->expects($this->once())
            ->method('createView')
            ->willReturn($formView);

        $this->formFactory->expects($this->once())
            ->method('create')
            ->with(PaymentProfileDTOType::class, $paymentProfileDTO, $expectedOptions)
            ->willReturn($form);

        $form = $this->provider->getPaymentProfileDTOFormView($paymentProfileDTO);

        $this->assertInstanceOf(FormView::class, $form);

        $formSecondCall = $this->provider->getPaymentProfileDTOFormView($paymentProfileDTO);
        $this->assertSame($form, $formSecondCall);
    }

    /**
     * @dataProvider paymentProfileDTOProvider
     */
    public function testGetPaymentProfileDTOForm(PaymentProfileDTO $paymentProfileDTO)
    {
        $this->configProvider->expects($this->any())
            ->method('getConfig')
            ->willReturn(new AuthorizeNetConfig(self::$config));

        $paymentProfile = $paymentProfileDTO->getProfile();
        $type = $paymentProfile->getType();
        $id = $paymentProfile->getId();
        $this->router->expects($this->any())
            ->method('generate')
            ->willReturnMap([
                [PaymentProfileDTOFormProvider::PAYMENT_PROFILE_CREATE_ROUTE_NAME, ['type' => $type], 1, 'create_link'],
                [PaymentProfileDTOFormProvider::PAYMENT_PROFILE_UPDATE_ROUTE_NAME, ['id' => $id], 1, 'update_link']
            ]);

        $config = new AuthorizeNetConfig(self::$config);
        $expectedOptions = [
            'action' => $id ? 'update_link' : 'create_link',
            'requireCvvEntryEnabled' => $config->isRequireCvvEntryEnabled(),
            'allowed_account_types' => $config->getECheckAccountTypes(),
            'paymentProfileComponentOptions' => [
                'allowedCreditCards' => $config->getAllowedCreditCards(),
                'clientKey' => $config->getClientKey(),
                'apiLoginID' => $config->getApiLoginId(),
                'testMode' => $config->isTestMode(),
            ]
        ];

        $form = $this->createMock(Form::class);

        $this->formFactory->expects($this->once())
            ->method('create')
            ->with(PaymentProfileDTOType::class, $paymentProfileDTO, $expectedOptions)
            ->willReturn($form);

        $form = $this->provider->getPaymentProfileDTOForm($paymentProfileDTO);

        $this->assertInstanceOf(Form::class, $form);

        $formSecondCall = $this->provider->getPaymentProfileDTOForm($paymentProfileDTO);
        $this->assertSame($form, $formSecondCall);
    }

    public function paymentProfileDTOProvider(): array
    {
        $newDTO = new PaymentProfileDTO();
        $existingDTO = new PaymentProfileDTO();
        $profile = $this->getEntity(CustomerPaymentProfile::class, ['id' => 1]);
        $profile->setName('test');
        $existingDTO->setProfile($profile);

        return [
            'new DTO (create action)' => [
                'paymentProfileDTO' => $newDTO
            ],
            'existing DTO (update action)' => [
                'paymentProfileDTO' => $existingDTO
            ]
        ];
    }
}
