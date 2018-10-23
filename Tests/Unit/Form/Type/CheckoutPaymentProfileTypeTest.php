<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\Form\Type;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\AuthorizeNetBundle\Entity\CustomerPaymentProfile;
use Oro\Bundle\AuthorizeNetBundle\Entity\CustomerProfile;
use Oro\Bundle\AuthorizeNetBundle\Form\Type\CheckoutPaymentProfileType;
use Oro\Bundle\AuthorizeNetBundle\Provider\CustomerProfileProvider;
use Oro\Bundle\AuthorizeNetBundle\Provider\PaymentProfileProvider;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\FormBundle\Form\Extension\TooltipFormExtension;
use Oro\Bundle\TranslationBundle\Translation\Translator;
use Oro\Component\Testing\Unit\EntityTrait;
use Oro\Component\Testing\Unit\PreloadedExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Test\FormIntegrationTestCase;
use Symfony\Component\Validator\Validation;

class CheckoutPaymentProfileTypeTest extends FormIntegrationTestCase
{
    use EntityTrait;

    const PROFILE_LABEL_MASK = '%s (ends with %s)';
    const NEW_CREDIT_CARD_LABEL = 'new card';
    const NEW_BANK_ACCOUNT_LABEL = 'new bank account';

    /** @var CheckoutPaymentProfileType */
    protected $formType;

    /** @var  Translator|\PHPUnit\Framework\MockObject\MockObject */
    private $translator;

    /** @var CustomerProfileProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $customerProfileProvider;

    /** @var PaymentProfileProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $paymentProfileProvider;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->translator = $this->createMock(Translator::class);
        $this->customerProfileProvider = $this->createMock(CustomerProfileProvider::class);
        $this->paymentProfileProvider = $this->createMock(PaymentProfileProvider::class);

        $this->formType = new CheckoutPaymentProfileType(
            $this->customerProfileProvider,
            $this->paymentProfileProvider,
            $this->translator
        );
        parent::setUp();
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensions()
    {
        /** @var ConfigProvider|\PHPUnit\Framework\MockObject\MockObject $configProvider */
        $configProvider = $this->createMock(ConfigProvider::class);

        return [
            new PreloadedExtension(
                [
                    CheckoutPaymentProfileType::class => $this->formType
                ],
                [
                    FormType::class =>
                        [
                            new TooltipFormExtension($configProvider, $this->translator)
                        ]
                ]
            ),
            new ValidatorExtension(Validation::createValidator())
        ];
    }

    public function testProfileFieldDefaultValueWithDefaultProfile()
    {
        $customerProfile = $this->buildCustomerProfile();
        /** @var CustomerPaymentProfile $lastPaymentProfile */
        $lastPaymentProfile = $customerProfile->getPaymentProfiles()->last();
        $lastPaymentProfile->setDefault(true);

        $this->customerProfileProvider
            ->expects($this->once())
            ->method('findCustomerProfile')
            ->willReturn($customerProfile);

        $externalIds = $customerProfile->getPaymentProfiles()->map(function (CustomerPaymentProfile $paymentProfile) {
            return $paymentProfile->getCustomerPaymentProfileId();
        })->toArray();

        $this->paymentProfileProvider
            ->expects($this->once())
            ->method('getPaymentProfileExternalIds')
            ->willReturn($externalIds);

        $profileForm = $this->factory->create(CheckoutPaymentProfileType::class)->get('profile');
        $profileFormOptions = $profileForm->getConfig()->getOptions();

        $this->assertSame($lastPaymentProfile, $profileForm->getData());
        $this->assertSame(
            array_merge($customerProfile->getPaymentProfiles()->toArray(), [null]),
            $profileFormOptions['choices']
        );
    }

    public function testProfileFieldDefaultValueNoCustomerProfile()
    {
        $this->customerProfileProvider
            ->expects($this->once())
            ->method('findCustomerProfile')
            ->willReturn(null);

        $profileForm = $this->factory->create(CheckoutPaymentProfileType::class)->get('profile');
        $profileFormOptions = $profileForm->getConfig()->getOptions();

        $this->assertNull($profileForm->getData());
        $this->assertSame([null], $profileFormOptions['choices']);
    }

    public function testProfileFieldDefaultValueWithoutDefaultProfile()
    {
        $customerProfile = $this->buildCustomerProfile();
        /** @var CustomerPaymentProfile $lastPaymentProfile */
        $firstPaymentProfile = $customerProfile->getPaymentProfiles()->first();

        $this->customerProfileProvider
            ->expects($this->once())
            ->method('findCustomerProfile')
            ->willReturn($customerProfile);

        $externalIds = $customerProfile->getPaymentProfiles()->map(function (CustomerPaymentProfile $paymentProfile) {
            return $paymentProfile->getCustomerPaymentProfileId();
        })->toArray();

        $this->paymentProfileProvider
            ->expects($this->once())
            ->method('getPaymentProfileExternalIds')
            ->willReturn($externalIds);

        $profileForm = $this->factory->create(CheckoutPaymentProfileType::class)->get('profile');
        $profileFormOptions = $profileForm->getConfig()->getOptions();

        $this->assertSame($firstPaymentProfile, $profileForm->getData());
        $this->assertSame(
            array_merge($customerProfile->getPaymentProfiles()->toArray(), [null]),
            $profileFormOptions['choices']
        );
    }

    public function testProfileFieldLabels()
    {
        $customerProfile = $this->buildCustomerProfile();

        $this->customerProfileProvider
            ->expects($this->once())
            ->method('findCustomerProfile')
            ->willReturn($customerProfile);

        $externalIds = $customerProfile->getPaymentProfiles()->map(function (CustomerPaymentProfile $paymentProfile) {
            return $paymentProfile->getCustomerPaymentProfileId();
        })->toArray();

        $this->paymentProfileProvider
            ->expects($this->once())
            ->method('getPaymentProfileExternalIds')
            ->willReturn($externalIds);

        $this->translator
            ->method('trans')
            ->willReturnMap($this->buildTranslationMap($customerProfile));

        $form = $this->factory->create(CheckoutPaymentProfileType::class);
        $profileFormOptions = $form->get('profile')->getConfig()->getOptions();

        $actualLabels = array_map($profileFormOptions['choice_label'], $profileFormOptions['choices']);

        foreach ($customerProfile->getPaymentProfiles() as $paymentProfile) {
            $expectedLabels[] = sprintf(
                self::PROFILE_LABEL_MASK,
                $paymentProfile->getName(),
                $paymentProfile->getLastDigits()
            );
        }
        $expectedLabels[] = self::NEW_CREDIT_CARD_LABEL;

        $this->assertEquals($expectedLabels, $actualLabels);
    }

    /**
     * @param array $submittedData
     * @param mixed $expectedData
     * @param mixed $defaultData
     * @param array $options
     * @param bool $isValid
     * @param CustomerProfile|null $customerProfile
     * @dataProvider submitProvider
     */
    public function testSubmit(
        $submittedData,
        $expectedData,
        $defaultData = null,
        $options = [],
        $isValid = true,
        CustomerProfile $customerProfile = null
    ) {
        $this->customerProfileProvider
            ->expects($this->once())
            ->method('findCustomerProfile')
            ->willReturn($customerProfile);

        $externalIds = $customerProfile->getPaymentProfiles()->map(function (CustomerPaymentProfile $paymentProfile) {
            return $paymentProfile->getCustomerPaymentProfileId();
        })->toArray();

        $this->paymentProfileProvider
            ->expects($this->once())
            ->method('getPaymentProfileExternalIds')
            ->willReturn($externalIds);

        $this->translator
            ->method('trans')
            ->willReturnMap($this->buildTranslationMap($customerProfile));

        $form = $this->factory->create(CheckoutPaymentProfileType::class, $defaultData, $options);

        $this->assertEquals($defaultData, $form->getData());

        $form->submit($submittedData);
        $this->assertEquals($isValid, $form->isValid());
        $this->assertEquals($expectedData, $form->getData());
    }

    /**
     * @return array
     */
    public function submitProvider()
    {
        $customerProfile = $this->buildCustomerProfile();

        return [
            'empty data valid' => [
                'submittedData' => [],
                'expectedData' => [
                    'profile' => null,
                    'saveProfile' => null
                ],
                'defaultData' => null,
                'options' => [],
                'isValid' => true,
                'customerProfile' => $customerProfile
            ],
            'full data valid' => [
                'submittedData' => [
                    'profile' => '1',
                    'saveProfile' => '1'
                ],
                'expectedData' => [
                    'profile' => $customerProfile->getPaymentProfiles()->first(),
                    'saveProfile' => true
                ],
                'defaultData' => null,
                'options' => [],
                'isValid' => true,
                'customerProfile' => $customerProfile
            ]
        ];
    }

    /**
     * @param CustomerProfile $customerProfile
     * @return array
     */
    private function buildTranslationMap(CustomerProfile $customerProfile)
    {
        $map[] = [
            'oro.authorize_net.frontend.payment_profile.checkout.new_creditcard_choice.label',
            [],
            null,
            null,
            self::NEW_CREDIT_CARD_LABEL
        ];

        $map[] = [
            'oro.authorize_net.frontend.payment_profile.checkout.new_echeck_choice.label',
            [],
            null,
            null,
            self::NEW_BANK_ACCOUNT_LABEL
        ];

        foreach ($customerProfile->getPaymentProfiles() as $paymentProfile) {
            $name = $paymentProfile->getName();
            $lastDigits = $paymentProfile->getLastDigits();

            $map[] = [
                'oro.authorize_net.frontend.payment_profile.checkout.profile_choice.label',
                [
                    '%name%' => $name,
                    '%lastDigits%' => $lastDigits
                ],
                null,
                null,
                sprintf(self::PROFILE_LABEL_MASK, $name, $lastDigits)
            ];
        }

        return $map;
    }

    /**
     * @return CustomerProfile
     */
    private function buildCustomerProfile()
    {
        $paymentProfile1 = $this->getEntity(CustomerPaymentProfile::class, [
            'id' => 1,
            'name' => 'profile 1',
            'lastDigits' => '1111',
            'customerPaymentProfileId' => 'external-id-1',
            'default' => false
        ]);

        $paymentProfile2 = $this->getEntity(CustomerPaymentProfile::class, [
            'id' => 2,
            'name' => 'profile 2',
            'lastDigits' => '2222',
            'customerPaymentProfileId' => 'external-id-2',
            'default' => false
        ]);

        $customerProfile = $this->getEntity(CustomerProfile::class, [
            'paymentProfiles' => new ArrayCollection([$paymentProfile1, $paymentProfile2])
        ]);

        return $customerProfile;
    }
}
