<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\Form\Type;

use Oro\Bundle\AuthorizeNetBundle\Entity\CustomerPaymentProfile;
use Oro\Bundle\AuthorizeNetBundle\Entity\CustomerProfile;
use Oro\Bundle\AuthorizeNetBundle\Form\Type\CheckoutPaymentProfileType;
use Oro\Bundle\AuthorizeNetBundle\Provider\CustomerProfileProvider;
use Oro\Bundle\AuthorizeNetBundle\Provider\PaymentProfileProvider;
use Oro\Bundle\FormBundle\Tests\Unit\Stub\TooltipFormExtensionStub;
use Oro\Component\Testing\Unit\EntityTrait;
use Oro\Component\Testing\Unit\PreloadedExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Test\FormIntegrationTestCase;
use Symfony\Component\Validator\Validation;
use Symfony\Contracts\Translation\TranslatorInterface;

class CheckoutPaymentProfileTypeTest extends FormIntegrationTestCase
{
    use EntityTrait;

    private const PROFILE_LABEL_MASK = '%s (ends with %s)';
    private const NEW_CREDIT_CARD_LABEL = 'new card';
    private const NEW_BANK_ACCOUNT_LABEL = 'new bank account';

    /** @var CheckoutPaymentProfileType */
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

        $this->formType = new CheckoutPaymentProfileType(
            $this->customerProfileProvider,
            $this->paymentProfileProvider,
            $this->translator
        );
        parent::setUp();
    }

    #[\Override]
    protected function getExtensions(): array
    {
        return [
            new PreloadedExtension(
                [
                    $this->formType
                ],
                [
                    FormType::class => [new TooltipFormExtensionStub($this)]
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

        $this->customerProfileProvider->expects($this->once())
            ->method('findCustomerProfile')
            ->willReturn($customerProfile);

        $externalIds = $customerProfile->getPaymentProfiles()->map(function (CustomerPaymentProfile $paymentProfile) {
            return $paymentProfile->getCustomerPaymentProfileId();
        })->toArray();

        $this->paymentProfileProvider->expects($this->once())
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
        $this->customerProfileProvider->expects($this->once())
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

        $this->customerProfileProvider->expects($this->once())
            ->method('findCustomerProfile')
            ->willReturn($customerProfile);

        $externalIds = $customerProfile->getPaymentProfiles()->map(function (CustomerPaymentProfile $paymentProfile) {
            return $paymentProfile->getCustomerPaymentProfileId();
        })->toArray();

        $this->paymentProfileProvider->expects($this->once())
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

        $this->customerProfileProvider->expects($this->once())
            ->method('findCustomerProfile')
            ->willReturn($customerProfile);

        $externalIds = $customerProfile->getPaymentProfiles()->map(function (CustomerPaymentProfile $paymentProfile) {
            return $paymentProfile->getCustomerPaymentProfileId();
        })->toArray();

        $this->paymentProfileProvider->expects($this->once())
            ->method('getPaymentProfileExternalIds')
            ->willReturn($externalIds);

        $this->translator->expects($this->any())
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
     * @dataProvider submitProvider
     */
    public function testSubmit(
        array            $submittedData,
        array            $expectedData,
        ?array           $defaultData = null,
        array            $options = [],
        bool             $isValid = true,
        ?CustomerProfile $customerProfile = null
    ) {
        $this->customerProfileProvider->expects($this->once())
            ->method('findCustomerProfile')
            ->willReturn($customerProfile);

        $externalIds = $customerProfile->getPaymentProfiles()->map(function (CustomerPaymentProfile $paymentProfile) {
            return $paymentProfile->getCustomerPaymentProfileId();
        })->toArray();

        $this->paymentProfileProvider->expects($this->once())
            ->method('getPaymentProfileExternalIds')
            ->willReturn($externalIds);

        $this->translator->expects($this->any())
            ->method('trans')
            ->willReturnMap($this->buildTranslationMap($customerProfile));

        $form = $this->factory->create(CheckoutPaymentProfileType::class, $defaultData, $options);

        $this->assertEquals($defaultData, $form->getData());

        $form->submit($submittedData);
        $this->assertEquals($isValid, $form->isValid());
        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($expectedData, $form->getData());
    }

    public function submitProvider(): array
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

    private function buildTranslationMap(CustomerProfile $customerProfile): array
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

    private function buildCustomerProfile(): CustomerProfile
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

        $customerProfile = new CustomerProfile();
        $customerProfile->addPaymentProfile($paymentProfile1);
        $customerProfile->addPaymentProfile($paymentProfile2);

        return $customerProfile;
    }
}
