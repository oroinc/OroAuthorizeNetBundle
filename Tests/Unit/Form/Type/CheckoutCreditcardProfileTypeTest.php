<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\Form\Type;

use Oro\Bundle\AuthorizeNetBundle\Entity\CustomerPaymentProfile;
use Oro\Bundle\AuthorizeNetBundle\Entity\CustomerProfile;
use Oro\Bundle\AuthorizeNetBundle\Form\Type\CheckoutCredicardProfileType;
use Oro\Bundle\AuthorizeNetBundle\Form\Type\CheckoutPaymentProfileType;
use Oro\Bundle\AuthorizeNetBundle\Form\Type\CreditCardCvvType;
use Oro\Bundle\AuthorizeNetBundle\Form\Type\CreditCardType;
use Oro\Bundle\AuthorizeNetBundle\Provider\CustomerProfileProvider;
use Oro\Bundle\AuthorizeNetBundle\Provider\PaymentProfileProvider;
use Oro\Bundle\FormBundle\Tests\Unit\Stub\TooltipFormExtensionStub;
use Oro\Component\Testing\Unit\EntityTrait;
use Oro\Component\Testing\Unit\PreloadedExtension;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Test\FormIntegrationTestCase;
use Symfony\Component\Validator\Validation;
use Symfony\Contracts\Translation\TranslatorInterface;

class CheckoutCreditcardProfileTypeTest extends FormIntegrationTestCase
{
    use EntityTrait;

    private const PROFILE_LABEL_MASK = '%s (ends with %s)';
    private const NEW_CARD_LABEL = 'new card';
    private const PROFILE_TYPE = CustomerPaymentProfile::TYPE_CREDITCARD;

    /** @var CheckoutCredicardProfileType */
    private $formType;

    /** @var TranslatorInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $translator;

    /** @var CustomerProfileProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $customerProfileProvider;

    /** @var PaymentProfileProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $paymentProfileProvider;

    #[\Override]
    protected function setUp(): void
    {
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->customerProfileProvider = $this->createMock(CustomerProfileProvider::class);
        $this->paymentProfileProvider = $this->createMock(PaymentProfileProvider::class);

        $this->formType = new CheckoutCredicardProfileType();
        parent::setUp();
    }

    #[\Override]
    protected function getExtensions(): array
    {
        return [
            new PreloadedExtension(
                [
                    $this->formType,
                    new CreditCardType(),
                    new CheckoutPaymentProfileType(
                        $this->customerProfileProvider,
                        $this->paymentProfileProvider,
                        $this->translator
                    ),
                    new CreditCardCvvType($this->translator),
                ],
                [
                    CheckboxType::class => [new TooltipFormExtensionStub($this)]
                ]
            ),
            new ValidatorExtension(Validation::createValidator())
        ];
    }

    /**
     * @dataProvider formConfigurationProvider
     */
    public function testFormConfiguration(array $presentFields, array $notPresentFields, array $formOptions)
    {
        $form = $this->factory->create(CheckoutCredicardProfileType::class, null, $formOptions);

        foreach ($presentFields as $fieldName) {
            $this->assertTrue($form->has($fieldName));
        }

        foreach ($notPresentFields as $fieldName) {
            $this->assertFalse($form->has($fieldName));
        }
    }

    public function testProfileFieldDefaultValueWithDefaultProfile()
    {
        $customerProfile = $this->buildCustomerProfile();
        /** @var CustomerPaymentProfile $lastPaymentProfile */
        $lastPaymentProfile = $customerProfile->getPaymentProfilesByType(self::PROFILE_TYPE)->last();
        $lastPaymentProfile->setDefault(true);

        $this->customerProfileProvider->expects($this->once())
            ->method('findCustomerProfile')
            ->willReturn($customerProfile);

        $externalIds = $customerProfile->getPaymentProfilesByType(self::PROFILE_TYPE)
            ->map(function (CustomerPaymentProfile $paymentProfile) {
                return $paymentProfile->getCustomerPaymentProfileId();
            })->toArray();

        $this->paymentProfileProvider->expects($this->once())
            ->method('getPaymentProfileExternalIds')
            ->willReturn($externalIds);

        $profileForm = $this->factory->create(CheckoutCredicardProfileType::class)->get('profile');
        $profileFormOptions = $profileForm->getConfig()->getOptions();

        $this->assertSame($lastPaymentProfile, $profileForm->getData());
        $this->assertSame(
            array_merge($customerProfile->getPaymentProfilesByType(self::PROFILE_TYPE)->toArray(), [null]),
            $profileFormOptions['choices']
        );
    }

    public function testProfileFieldDefaultValueNoCustomerProfile()
    {
        $this->customerProfileProvider->expects($this->once())
            ->method('findCustomerProfile')
            ->willReturn(null);

        $profileForm = $this->factory->create(CheckoutCredicardProfileType::class)->get('profile');
        $profileFormOptions = $profileForm->getConfig()->getOptions();

        $this->assertNull($profileForm->getData());
        $this->assertSame([null], $profileFormOptions['choices']);
    }

    public function testProfileFieldDefaultValueWithoutDefaultProfile()
    {
        $customerProfile = $this->buildCustomerProfile();
        /** @var CustomerPaymentProfile $lastPaymentProfile */
        $firstPaymentProfile = $customerProfile->getPaymentProfilesByType(self::PROFILE_TYPE)->first();

        $this->customerProfileProvider->expects($this->once())
            ->method('findCustomerProfile')
            ->willReturn($customerProfile);

        $externalIds = $customerProfile->getPaymentProfilesByType(self::PROFILE_TYPE)
            ->map(function (CustomerPaymentProfile $paymentProfile) {
                return $paymentProfile->getCustomerPaymentProfileId();
            })->toArray();

        $this->paymentProfileProvider->expects($this->once())
            ->method('getPaymentProfileExternalIds')
            ->willReturn($externalIds);

        $profileForm = $this->factory->create(CheckoutCredicardProfileType::class)->get('profile');
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

        $this->customerProfileProvider->expects($this->once())
            ->method('findCustomerProfile')
            ->willReturn($customerProfile);

        $externalIds = $customerProfile->getPaymentProfilesByType(self::PROFILE_TYPE)
            ->map(function (CustomerPaymentProfile $paymentProfile) {
                return $paymentProfile->getCustomerPaymentProfileId();
            })->toArray();

        $this->paymentProfileProvider->expects($this->once())
            ->method('getPaymentProfileExternalIds')
            ->willReturn($externalIds);

        // Can't use 'willReturnMap' as it returns null for default cases while 'trans' method must return string
        $translationMap = $this->buildTranslationMap($customerProfile);
        $this->translator->expects($this->any())
            ->method('trans')
            ->willReturnCallback(function (...$args) use ($translationMap) {
                foreach ($translationMap as $entry) {
                    $returnValue = array_pop($entry);
                    if ($args === $entry) {
                        return $returnValue;
                    }
                }
                return '';
            });

        $form = $this->factory->create(CheckoutCredicardProfileType::class);
        $profileFormOptions = $form->get('profile')->getConfig()->getOptions();

        $actualLabels = array_map($profileFormOptions['choice_label'], $profileFormOptions['choices']);

        foreach ($customerProfile->getPaymentProfilesByType(self::PROFILE_TYPE) as $paymentProfile) {
            $expectedLabels[] = sprintf(
                self::PROFILE_LABEL_MASK,
                $paymentProfile->getName(),
                $paymentProfile->getLastDigits()
            );
        }
        $expectedLabels[] = self::NEW_CARD_LABEL;

        $this->assertEquals($expectedLabels, $actualLabels);
    }

    public function formConfigurationProvider(): array
    {
        return [
            'cvv required' => [
                'presentFields' => ['profile', 'saveProfile', 'profileCVV', 'paymentData'],
                'notPresentFields' => [],
                'formOptions' => ['requireCvvEntryEnabled' => true]
            ],
            'cvv not required' => [
                'formFields' => ['profile', 'saveProfile', 'paymentData'],
                'notPresentFields' => ['profileCVV'],
                'formOptions' => ['requireCvvEntryEnabled' => false]
            ]
        ];
    }

    private function buildTranslationMap(CustomerProfile $customerProfile): array
    {
        $map[] = [
            'oro.authorize_net.frontend.payment_profile.checkout.new_creditcard_choice.label',
            [],
            null,
            null,
            self::NEW_CARD_LABEL
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

    private function buildCustomerProfile(): CustomerProfile
    {
        $paymentProfile1 = $this->getEntity(CustomerPaymentProfile::class, [
            'id' => 1,
            'name' => 'profile 1',
            'lastDigits' => '1111',
            'default' => false,
            'type' => CustomerPaymentProfile::TYPE_CREDITCARD
        ]);

        $paymentProfile2 = $this->getEntity(CustomerPaymentProfile::class, [
            'id' => 2,
            'name' => 'profile 2',
            'lastDigits' => '2222',
            'default' => false,
            'type' => CustomerPaymentProfile::TYPE_CREDITCARD
        ]);

        $paymentProfile3 = $this->getEntity(CustomerPaymentProfile::class, [
            'id' => 3,
            'name' => 'profile 3',
            'lastDigits' => '3333',
            'default' => false,
            'type' => CustomerPaymentProfile::TYPE_ECHECK
        ]);

        $customerProfile = new CustomerProfile();
        $customerProfile->addPaymentProfile($paymentProfile1);
        $customerProfile->addPaymentProfile($paymentProfile2);
        $customerProfile->addPaymentProfile($paymentProfile3);

        return $customerProfile;
    }
}
