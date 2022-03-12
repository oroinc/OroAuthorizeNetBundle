<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\Form\Type;

use Oro\Bundle\AuthorizeNetBundle\Entity\AuthorizeNetSettings;
use Oro\Bundle\AuthorizeNetBundle\Form\Extension\EnabledCIMWebsitesSelectExtension;
use Oro\Bundle\AuthorizeNetBundle\Form\Type\AuthorizeNetSettingsType;
use Oro\Bundle\AuthorizeNetBundle\Settings\DataProvider\CardTypesDataProviderInterface;
use Oro\Bundle\AuthorizeNetBundle\Settings\DataProvider\PaymentActionsDataProviderInterface;
use Oro\Bundle\FormBundle\Form\Type\OroEncodedPlaceholderPasswordType;
use Oro\Bundle\FormBundle\Tests\Unit\Stub\TooltipFormExtensionStub;
use Oro\Bundle\LocaleBundle\Form\Type\LocalizedFallbackValueCollectionType;
use Oro\Bundle\LocaleBundle\Tests\Unit\Form\Type\Stub\LocalizedFallbackValueCollectionTypeStub;
use Oro\Bundle\SecurityBundle\Encoder\SymmetricCrypterInterface;
use Oro\Bundle\SecurityBundle\Form\DataTransformer\Factory\CryptedDataTransformerFactoryInterface;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Oro\Bundle\WebsiteBundle\Provider\WebsiteProviderInterface;
use Oro\Component\Testing\Unit\Form\Type\Stub\EntityType as EntityTypeStub;
use Oro\Component\Testing\Unit\FormIntegrationTestCase;
use Oro\Component\Testing\Unit\PreloadedExtension;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Validation;

class AuthorizeNetSettingsTypeTest extends FormIntegrationTestCase
{
    private const CARD_TYPES = [
        'visa',
        'mastercard',
    ];

    private const PAYMENT_ACTION = 'authorize';

    /** @var AuthorizeNetSettingsType */
    private $formType;

    /** @var CryptedDataTransformerFactoryInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $cryptedDataTransformerFactory;

    protected function setUp(): void
    {
        $this->prepareForm();
        parent::setUp();
    }

    private function prepareForm(): void
    {
        $cardTypesDataProvider = $this->createMock(CardTypesDataProviderInterface::class);
        $cardTypesDataProvider->expects($this->any())
            ->method('getCardTypes')
            ->willReturn(self::CARD_TYPES);

        $actionsDataProvider = $this->createMock(PaymentActionsDataProviderInterface::class);
        $actionsDataProvider->expects($this->any())
            ->method('getPaymentActions')
            ->willReturn([self::PAYMENT_ACTION, 'charge']);

        $this->cryptedDataTransformerFactory = $this->createMock(CryptedDataTransformerFactoryInterface::class);
        $this->formType = new AuthorizeNetSettingsType(
            $this->getTranslator(),
            $this->cryptedDataTransformerFactory,
            $cardTypesDataProvider,
            $actionsDataProvider
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function getExtensions(): array
    {
        $websiteProvider = $this->createMock(WebsiteProviderInterface::class);
        $websiteProvider->expects($this->any())
            ->method('getWebsiteIds')
            ->willReturn([1,2]);

        return [
            new PreloadedExtension(
                [
                    $this->formType,
                    new OroEncodedPlaceholderPasswordType($this->createMock(SymmetricCrypterInterface::class)),
                    EntityType::class => new EntityTypeStub([]),
                    LocalizedFallbackValueCollectionType::class => new LocalizedFallbackValueCollectionTypeStub(),
                ],
                [
                    CheckboxType::class => [new TooltipFormExtensionStub($this)],
                    EntityTypeStub::class => [new TooltipFormExtensionStub($this)],
                    TextareaType::class => [new TooltipFormExtensionStub($this)],
                    ChoiceType::class => [new TooltipFormExtensionStub($this)],
                    LocalizedFallbackValueCollectionTypeStub::class => [new TooltipFormExtensionStub($this)],
                    AuthorizeNetSettingsType::class => [
                        new EnabledCIMWebsitesSelectExtension(
                            $websiteProvider,
                            $this->createMock(WebsiteManager::class)
                        ),
                    ]
                ]
            ),
            new ValidatorExtension(Validation::createValidator()),
        ];
    }

    public function testGetBlockPrefixReturnsCorrectString()
    {
        $this->assertSame(AuthorizeNetSettingsType::BLOCK_PREFIX, $this->formType->getBlockPrefix());
    }

    public function testSubmit()
    {
        $submitData = [
            'creditCardLabels' => [['string' => 'creditCard']],
            'creditCardShortLabels' => [['string' => 'creditCardShort']],
            'allowedCreditCardTypes' => self::CARD_TYPES,
            'creditCardPaymentAction' => self::PAYMENT_ACTION,
            'apiLoginId' => 'some login',
            'transactionKey' => 'some transaction key',
            'clientKey' => 'some client key',
            'authNetTestMode' => true,
            'authNetRequireCVVEntry' => false,
            'enabledCIM' => false,
            'eCheckEnabled' => false,
            'eCheckLabels' => [['string' => 'eCheck Label']],
            'eCheckShortLabels' => [['string' => 'eCheck Short']],
            'eCheckAccountTypes' => ['checking'],
            'eCheckConfirmationText' => 'some text',
            'allowHoldTransaction' => true
        ];

        $this->cryptedDataTransformerFactory->expects($this->any())
            ->method('create')
            ->willReturnCallback(function () {
                return $this->createMock(DataTransformerInterface::class);
            });

        $authorizeNetSettings = new AuthorizeNetSettings();

        $form = $this->factory->create(
            AuthorizeNetSettingsType::class,
            $authorizeNetSettings,
            [
                'constraints' => []
            ]
        );

        $form->submit($submitData);

        $this->assertTrue($form->isValid());
        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($authorizeNetSettings, $form->getData());
    }

    public function testConfigureOptions()
    {
        $resolver = $this->createMock(OptionsResolver::class);
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with(['data_class' => AuthorizeNetSettings::class]);

        $this->formType->configureOptions($resolver);
    }
}
