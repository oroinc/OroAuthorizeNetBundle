<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\Form\Type;

use Oro\Bundle\AuthorizeNetBundle\Entity\AuthorizeNetSettings;
use Oro\Bundle\AuthorizeNetBundle\Form\Type\AuthorizeNetSettingsType;
use Oro\Bundle\AuthorizeNetBundle\Settings\DataProvider\CardTypesDataProviderInterface;
use Oro\Bundle\AuthorizeNetBundle\Settings\DataProvider\PaymentActionsDataProviderInterface;
use Oro\Bundle\FormBundle\Form\Type\OroEncodedPlaceholderPasswordType;
use Oro\Bundle\LocaleBundle\Form\Type\LocalizedFallbackValueCollectionType;
use Oro\Bundle\LocaleBundle\Tests\Unit\Form\Type\Stub\LocalizedFallbackValueCollectionTypeStub;
use Oro\Bundle\SecurityBundle\Encoder\SymmetricCrypterInterface;
use Oro\Bundle\SecurityBundle\Form\DataTransformer\Factory\CryptedDataTransformerFactoryInterface;
use Oro\Component\Testing\Unit\FormIntegrationTestCase;
use Oro\Component\Testing\Unit\PreloadedExtension;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Validation;

class AuthorizeNetSettingsTypeTest extends FormIntegrationTestCase
{
    const CARD_TYPES = [
        'visa',
        'mastercard',
    ];
    const PAYMENT_ACTION = 'authorize';

    /**
     * @var AuthorizeNetSettingsType
     */
    private $formType;

    /**
     * @var CryptedDataTransformerFactoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $cryptedDataTransformerFactory;

    public function setUp()
    {
        $this->prepareForm();
        parent::setUp();
    }

    protected function prepareForm()
    {
        /** @var TranslatorInterface|\PHPUnit\Framework\MockObject\MockObject $translator */
        $translator = $this->createMock(TranslatorInterface::class);
        /** @var CardTypesDataProviderInterface|\PHPUnit\Framework\MockObject\MockObject $cardTypesDataProvider */
        $cardTypesDataProvider = $this->createMock(CardTypesDataProviderInterface::class);
        $cardTypesDataProvider->expects($this->any())
            ->method('getCardTypes')
            ->willReturn(self::CARD_TYPES);

        /** @var PaymentActionsDataProviderInterface|\PHPUnit\Framework\MockObject\MockObject $actionsDataProvider */
        $actionsDataProvider = $this->createMock(PaymentActionsDataProviderInterface::class);
        $actionsDataProvider->expects($this->any())
            ->method('getPaymentActions')
            ->willReturn(
                [
                    self::PAYMENT_ACTION,
                    'charge',
                ]
            );

        $this->cryptedDataTransformerFactory = $this->createMock(CryptedDataTransformerFactoryInterface::class);
        $this->formType = new AuthorizeNetSettingsType(
            $translator,
            $this->cryptedDataTransformerFactory,
            $cardTypesDataProvider,
            $actionsDataProvider
        );
    }

    /**
     * @return array
     */
    protected function getExtensions()
    {
        $localizedType = new LocalizedFallbackValueCollectionTypeStub();
        $encoder = $this->createEncoderMock();

        return [
            new PreloadedExtension(
                [
                    $this->formType,
                    LocalizedFallbackValueCollectionType::class => $localizedType,
                    new OroEncodedPlaceholderPasswordType($encoder),
                ],
                []
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
        ];

        $this->cryptedDataTransformerFactory
            ->expects(static::any())
            ->method('create')
            ->willReturnCallback(function () {
                return $this->createMock(DataTransformerInterface::class);
            });

        $authorizeNetSettings = new AuthorizeNetSettings();

        $form = $this->factory->create(AuthorizeNetSettingsType::class, $authorizeNetSettings);

        $form->submit($submitData);

        $this->assertTrue($form->isValid());
        $this->assertEquals($authorizeNetSettings, $form->getData());
    }

    public function testConfigureOptions()
    {
        /** @var OptionsResolver|\PHPUnit\Framework\MockObject\MockObject $resolver */
        $resolver = $this->createMock(OptionsResolver::class);
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with(
                [
                    'data_class' => AuthorizeNetSettings::class,
                ]
            );

        $this->formType->configureOptions($resolver);
    }

    /**
     * @return SymmetricCrypterInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private function createEncoderMock()
    {
        return $this->createMock(SymmetricCrypterInterface::class);
    }
}
