<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\Form\Type;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\AuthorizeNetBundle\Entity\CustomerPaymentProfile;
use Oro\Bundle\AuthorizeNetBundle\Entity\CustomerProfile;
use Oro\Bundle\AuthorizeNetBundle\Form\Type\BankAccountType;
use Oro\Bundle\AuthorizeNetBundle\Form\Type\CheckoutEcheckProfileType;
use Oro\Bundle\AuthorizeNetBundle\Form\Type\CheckoutPaymentProfileType;
use Oro\Bundle\AuthorizeNetBundle\Provider\CustomerProfileProvider;
use Oro\Bundle\AuthorizeNetBundle\Provider\PaymentProfileProvider;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\FormBundle\Form\Extension\TooltipFormExtension;
use Oro\Bundle\FormBundle\Tests\Unit\Stub\StripTagsExtensionStub;
use Oro\Bundle\TranslationBundle\Translation\Translator;
use Oro\Component\Testing\Unit\EntityTrait;
use Oro\Component\Testing\Unit\PreloadedExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Test\FormIntegrationTestCase;
use Symfony\Component\Validator\Validation;

class CheckoutEcheckProfileTypeTest extends FormIntegrationTestCase
{
    use EntityTrait;

    const PROFILE_LABEL_MASK = '%s (ends with %s)';
    const NEW_LABEL = 'new card';
    const PROFILE_TYPE = CustomerPaymentProfile::TYPE_ECHECK;
    const FORM_OPTIONS = [
        'profile_type' => self::PROFILE_TYPE
    ];

    /** @var CheckoutEcheckProfileType */
    protected $formType;

    /** @var Translator|\PHPUnit\Framework\MockObject\MockObject */
    private $translator;

    /** @var CustomerProfileProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $customerProfileProvider;

    /** @var PaymentProfileProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $paymentProfileProvider;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->translator = $this->createMock(Translator::class);
        $this->customerProfileProvider = $this->createMock(CustomerProfileProvider::class);
        $this->paymentProfileProvider = $this->createMock(PaymentProfileProvider::class);

        $this->formType = new CheckoutEcheckProfileType();
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
                    BankAccountType::class => new BankAccountType($this->translator),
                    CheckoutPaymentProfileType::class =>
                        new CheckoutPaymentProfileType(
                            $this->customerProfileProvider,
                            $this->paymentProfileProvider,
                            $this->translator
                        ),
                    CheckoutEcheckProfileType::class => $this->formType
                ],
                [
                    FormType::class => [
                        new StripTagsExtensionStub($this),
                        new TooltipFormExtension($configProvider, $this->translator)
                    ]
                ]
            ),
            new ValidatorExtension(Validation::createValidator())
        ];
    }

    /**
     * @dataProvider formConfigurationProvider
     * @param array $presentFields
     * @param array $notPresentFields
     * @param array $formOptions
     */
    public function testFormConfiguration(array $presentFields, array $notPresentFields, array $formOptions)
    {
        $form = $this->factory->create(CheckoutEcheckProfileType::class, null, $formOptions);

        foreach ($presentFields as $fieldname) {
            $this->assertTrue($form->has($fieldname));
        }

        foreach ($notPresentFields as $fieldname) {
            $this->assertFalse($form->has($fieldname));
        }
    }

    public function testProfileFieldDefaultValueWithDefaultProfile()
    {
        $customerProfile = $this->buildCustomerProfile();
        /** @var CustomerPaymentProfile $lastPaymentProfile */
        $lastPaymentProfile = $customerProfile->getPaymentProfilesByType(self::PROFILE_TYPE)->last();
        $lastPaymentProfile->setDefault(true);

        $this->customerProfileProvider
            ->expects($this->once())
            ->method('findCustomerProfile')
            ->willReturn($customerProfile);

        $externalIds = $customerProfile->getPaymentProfilesByType(self::PROFILE_TYPE)
            ->map(function (CustomerPaymentProfile $paymentProfile) {
                return $paymentProfile->getCustomerPaymentProfileId();
            })->toArray();

        $this->paymentProfileProvider
            ->expects($this->once())
            ->method('getPaymentProfileExternalIds')
            ->willReturn($externalIds);

        $form = $this->factory->create(CheckoutEcheckProfileType::class, null, self::FORM_OPTIONS);
        $profileForm = $form->get('profile');
        $profileFormOptions = $profileForm->getConfig()->getOptions();

        $this->assertSame($lastPaymentProfile, $profileForm->getData());
        $this->assertSame(
            array_merge($customerProfile->getPaymentProfilesByType(self::PROFILE_TYPE)->toArray(), [null]),
            $profileFormOptions['choices']
        );
    }

    public function testProfileFieldDefaultValueNoCustomerProfile()
    {
        $this->customerProfileProvider
            ->expects($this->once())
            ->method('findCustomerProfile')
            ->willReturn(null);

        $form = $this->factory->create(CheckoutEcheckProfileType::class, null, self::FORM_OPTIONS);
        $profileForm = $form->get('profile');
        $profileFormOptions = $profileForm->getConfig()->getOptions();

        $this->assertNull($profileForm->getData());
        $this->assertSame([null], $profileFormOptions['choices']);
    }

    public function testProfileFieldDefaultValueWithoutDefaultProfile()
    {
        $customerProfile = $this->buildCustomerProfile();
        /** @var CustomerPaymentProfile $lastPaymentProfile */
        $firstPaymentProfile = $customerProfile->getPaymentProfilesByType(self::PROFILE_TYPE)->first();

        $this->customerProfileProvider
            ->expects($this->once())
            ->method('findCustomerProfile')
            ->willReturn($customerProfile);

        $externalIds = $customerProfile->getPaymentProfilesByType(self::PROFILE_TYPE)
            ->map(function (CustomerPaymentProfile $paymentProfile) {
                return $paymentProfile->getCustomerPaymentProfileId();
            })->toArray();

        $this->paymentProfileProvider
            ->expects($this->once())
            ->method('getPaymentProfileExternalIds')
            ->willReturn($externalIds);

        $form = $this->factory->create(CheckoutEcheckProfileType::class, null, self::FORM_OPTIONS);
        $profileForm = $form->get('profile');
        $profileFormOptions = $profileForm->getConfig()->getOptions();

        $this->assertSame($firstPaymentProfile, $profileForm->getData());
        $this->assertSame(
            array_merge($customerProfile->getPaymentProfilesByType(self::PROFILE_TYPE)->toArray(), [null]),
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

        $externalIds = $customerProfile->getPaymentProfilesByType(self::PROFILE_TYPE)
            ->map(function (CustomerPaymentProfile $paymentProfile) {
                return $paymentProfile->getCustomerPaymentProfileId();
            })->toArray();

        $this->paymentProfileProvider
            ->expects($this->once())
            ->method('getPaymentProfileExternalIds')
            ->willReturn($externalIds);

        $this->translator
            ->method('trans')
            ->willReturnMap($this->buildTranslationMap($customerProfile));

        $form = $this->factory->create(CheckoutEcheckProfileType::class, null, self::FORM_OPTIONS);
        $profileFormOptions = $form->get('profile')->getConfig()->getOptions();

        $actualLabels = array_map($profileFormOptions['choice_label'], $profileFormOptions['choices']);

        foreach ($customerProfile->getPaymentProfilesByType(self::PROFILE_TYPE) as $paymentProfile) {
            $expectedLabels[] = sprintf(
                self::PROFILE_LABEL_MASK,
                $paymentProfile->getName(),
                $paymentProfile->getLastDigits()
            );
        }
        $expectedLabels[] = self::NEW_LABEL;

        $this->assertEquals($expectedLabels, $actualLabels);
    }

    /**
     * @return array
     */
    public function formConfigurationProvider()
    {
        return [
            'field list' => [
                'presentFields' => ['profile', 'saveProfile', 'paymentData'],
                'notPresentFields' => [],
                'formOptions' => self::FORM_OPTIONS
            ]
        ];
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

        $externalIds = $customerProfile->getPaymentProfilesByType(self::PROFILE_TYPE)
            ->map(function (CustomerPaymentProfile $paymentProfile) {
                return $paymentProfile->getCustomerPaymentProfileId();
            })->toArray();

        $this->paymentProfileProvider
            ->expects($this->once())
            ->method('getPaymentProfileExternalIds')
            ->willReturn($externalIds);

        $this->translator
            ->method('trans')
            ->willReturnMap($this->buildTranslationMap($customerProfile));

        $form = $this->factory->create(CheckoutEcheckProfileType::class, $defaultData, $options);

        $this->assertEquals($defaultData, $form->getData());

        $form->submit($submittedData);
        $this->assertEquals($isValid, $form->isValid());
        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($expectedData, $form->getData());
    }

    /**
     * @return array
     */
    public function submitProvider()
    {
        $customerProfile = $this->buildCustomerProfile();

        return [
            'empty data not valid' => [
                'submittedData' => [],
                'expectedData' => [
                    'profile' => null,
                    'saveProfile' => null,
                    'paymentData' => [
                        'accountType' => null,
                        'accountNumber' => null,
                        'routingNumber' => null,
                        'nameOnAccount' => null,
                        'bankName' => null
                    ]
                ],
                'defaultData' => null,
                'options' => self::FORM_OPTIONS,
                'isValid' => false,
                'customerProfile' => $customerProfile
            ],
            'full data valid' => [
                'submittedData' => [
                    'profile' => '1',
                    'saveProfile' => '1',
                    'paymentData' => [
                        'accountType' => 'checking',
                        'accountNumber' => '123456789',
                        'routingNumber' => '987654321',
                        'nameOnAccount' => 'John Doe',
                        'bankName' => 'ORO BANK'
                    ]
                ],
                'expectedData' => [
                    'profile' => $customerProfile->getPaymentProfiles()->first(),
                    'saveProfile' => true,
                    'paymentData' => [
                        'accountType' => 'checking',
                        'accountNumber' => '123456789',
                        'routingNumber' => '987654321',
                        'nameOnAccount' => 'John Doe',
                        'bankName' => 'ORO BANK'
                    ],
                ],
                'defaultData' => null,
                'options' => self::FORM_OPTIONS,
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
            'oro.authorize_net.frontend.payment_profile.checkout.new_echeck_choice.label',
            [],
            null,
            null,
            self::NEW_LABEL
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
            'default' => false,
            'type' => CustomerPaymentProfile::TYPE_ECHECK
        ]);

        $paymentProfile2 = $this->getEntity(CustomerPaymentProfile::class, [
            'id' => 2,
            'name' => 'profile 2',
            'lastDigits' => '2222',
            'default' => false,
            'type' => CustomerPaymentProfile::TYPE_ECHECK
        ]);

        $paymentProfile3 = $this->getEntity(CustomerPaymentProfile::class, [
            'id' => 3,
            'name' => 'profile 3',
            'lastDigits' => '3333',
            'default' => false,
            'type' => CustomerPaymentProfile::TYPE_CREDITCARD
        ]);

        $customerProfile = $this->getEntity(CustomerProfile::class, [
            'paymentProfiles' => new ArrayCollection([$paymentProfile1, $paymentProfile2, $paymentProfile3])
        ]);

        return $customerProfile;
    }
}
