<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Layout\DataProvider;

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
    protected $provider;

    /** @var FormFactoryInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $mockFormFactory;

    /** @var \PHPUnit\Framework\MockObject\MockObject|UrlGeneratorInterface */
    protected $router;

    /** @var \PHPUnit\Framework\MockObject\MockObject|CIMEnabledIntegrationConfigProvider */
    protected $configProvider;

    /** @var \PHPUnit\Framework\MockObject\MockObject|WebsiteManager */
    protected $websiteManager;

    /** @var array */
    protected static $config = [
        AuthorizeNetConfig::API_LOGIN_ID => 'login',
        AuthorizeNetConfig::CLIENT_KEY => 'key',
        AuthorizeNetConfig::TEST_MODE_KEY => true,
        AuthorizeNetConfig::ALLOWED_CREDIT_CARD_TYPES_KEY => ['visa'],
        AuthorizeNetConfig::REQUIRE_CVV_ENTRY_KEY => true,
        AuthorizeNetConfig::ECHECK_ACCOUNT_TYPES => ['test']
    ];

    protected function setUp()
    {
        $this->mockFormFactory = $this->createMock(FormFactoryInterface::class);
        $this->router = $this->createMock(UrlGeneratorInterface::class);

        $this->configProvider = $this->createMock(CIMEnabledIntegrationConfigProvider::class);

        $this->provider = new PaymentProfileDTOFormProvider(
            $this->mockFormFactory,
            $this->router,
            $this->configProvider
        );
    }

    /**
     * @param PaymentProfileDTO $paymentProfileDTO
     * @dataProvider paymentProfileDTOProvider
     */
    public function testGetPaymentProfileDTOFormView(PaymentProfileDTO $paymentProfileDTO)
    {
        $this->configProvider
            ->expects($this->any())
            ->method('getConfig')
            ->willReturn(new AuthorizeNetConfig(self::$config));

        $paymentProfile = $paymentProfileDTO->getProfile();
        $type = $paymentProfile->getType();
        $id = $paymentProfile->getId();
        $this
            ->router
            ->expects($this->any())
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

        $mockFormView = $this->createMock(FormView::class);

        $mockForm = $this->getMockBuilder(FormInterface::class)->getMock();
        $mockForm->expects($this->once())
            ->method('createView')
            ->willReturn($mockFormView);

        $this->mockFormFactory->expects($this->once())
            ->method('create')
            ->with(PaymentProfileDTOType::class, $paymentProfileDTO, $expectedOptions)
            ->willReturn($mockForm);

        $form = $this->provider->getPaymentProfileDTOFormView($paymentProfileDTO);

        $this->assertInstanceOf(FormView::class, $form);

        $formSecondCall = $this->provider->getPaymentProfileDTOFormView($paymentProfileDTO);
        $this->assertSame($form, $formSecondCall);
    }

    /**
     * @param PaymentProfileDTO $paymentProfileDTO
     * @dataProvider paymentProfileDTOProvider
     */
    public function testGetPaymentProfileDTOForm(PaymentProfileDTO $paymentProfileDTO)
    {
        $this->configProvider
            ->expects($this->any())
            ->method('getConfig')
            ->willReturn(new AuthorizeNetConfig(self::$config));

        $paymentProfile = $paymentProfileDTO->getProfile();
        $type = $paymentProfile->getType();
        $id = $paymentProfile->getId();
        $this
            ->router
            ->expects($this->any())
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

        $mockForm = $this->createMock(Form::class);

        $this->mockFormFactory->expects($this->once())
            ->method('create')
            ->with(PaymentProfileDTOType::class, $paymentProfileDTO, $expectedOptions)
            ->willReturn($mockForm);

        $form = $this->provider->getPaymentProfileDTOForm($paymentProfileDTO);

        $this->assertInstanceOf(Form::class, $form);

        $formSecondCall = $this->provider->getPaymentProfileDTOForm($paymentProfileDTO);
        $this->assertSame($form, $formSecondCall);
    }

    /**
     * @return array
     */
    public function paymentProfileDTOProvider()
    {
        $newDTO = new PaymentProfileDTO();
        $existingDTO = new PaymentProfileDTO();
        /** @var CustomerPaymentProfile $profile */
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
