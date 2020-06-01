<?php

namespace Oro\Bundle\AuthorizeNetBundle\Form\Type;

use Oro\Bundle\AuthorizeNetBundle\Entity\AuthorizeNetSettings;
use Oro\Bundle\AuthorizeNetBundle\Settings\DataProvider\CardTypesDataProviderInterface;
use Oro\Bundle\AuthorizeNetBundle\Settings\DataProvider\PaymentActionsDataProviderInterface;
use Oro\Bundle\FormBundle\Form\Type\OroEncodedPlaceholderPasswordType;
use Oro\Bundle\LocaleBundle\Form\Type\LocalizedFallbackValueCollectionType;
use Oro\Bundle\SecurityBundle\Form\DataTransformer\Factory\CryptedDataTransformerFactoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Form type for AuthorizeNet integration settings
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class AuthorizeNetSettingsType extends AbstractType
{
    const BLOCK_PREFIX = 'oro_authorize_net_settings';

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var CardTypesDataProviderInterface
     */
    protected $cardTypesDataProvider;

    /**
     * @var PaymentActionsDataProviderInterface
     */
    protected $paymentActionsDataProvider;

    /**
     * @var CryptedDataTransformerFactoryInterface
     */
    protected $cryptedDataTransformerFactory;

    /**
     * @param TranslatorInterface                    $translator
     * @param CryptedDataTransformerFactoryInterface $cryptedDataTransformerFactory
     * @param CardTypesDataProviderInterface         $cardTypesDataProvider
     * @param PaymentActionsDataProviderInterface    $paymentActionsDataProvider
     */
    public function __construct(
        TranslatorInterface $translator,
        CryptedDataTransformerFactoryInterface $cryptedDataTransformerFactory,
        CardTypesDataProviderInterface $cardTypesDataProvider,
        PaymentActionsDataProviderInterface $paymentActionsDataProvider
    ) {
        $this->translator = $translator;
        $this->cryptedDataTransformerFactory = $cryptedDataTransformerFactory;
        $this->cardTypesDataProvider = $cardTypesDataProvider;
        $this->paymentActionsDataProvider = $paymentActionsDataProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('creditCardLabels', LocalizedFallbackValueCollectionType::class, [
                'label' => 'oro.authorize_net.settings.credit_card_labels.label',
                'tooltip' => 'oro.authorize_net.settings.label.tooltip',
                'required' => true,
                'entry_options' => ['constraints' => [new NotBlank(), new Length(['max' => 255])]]
            ])
            ->add('creditCardShortLabels', LocalizedFallbackValueCollectionType::class, [
                'label' => 'oro.authorize_net.settings.credit_card_short_labels.label',
                'tooltip' => 'oro.authorize_net.settings.short_label.tooltip',
                'required' => true,
                'entry_options' => ['constraints' => [new NotBlank(), new Length(['max' => 255])]]
            ])
            ->add('creditCardPaymentAction', ChoiceType::class, [
                'choices' => $this->paymentActionsDataProvider->getPaymentActions(),
                'choice_label' => function ($action) {
                    return $this->translator->trans(
                        sprintf('oro.authorize_net.settings.payment_action.%s', $action)
                    );
                },
                'label' => 'oro.authorize_net.settings.credit_card_payment_action.label',
                'required' => true,
            ])
            ->add('allowedCreditCardTypes', ChoiceType::class, [
                'choices' => $this->cardTypesDataProvider->getCardTypes(),
                'choice_label' => function ($cardType) {
                    return $this->translator->trans(
                        sprintf('oro.authorize_net.settings.allowed_cc_types.%s', $cardType)
                    );
                },
                'label' => 'oro.authorize_net.settings.allowed_cc_types.label',
                'required' => true,
                'multiple' => true,
            ])
            ->add('apiLoginId', TextType::class, [
                'label' => 'oro.authorize_net.settings.api_login.label',
                'required' => true,
                'attr' => ['autocomplete' => 'off'],
            ])
            ->add('transactionKey', OroEncodedPlaceholderPasswordType::class, [
                'label' => 'oro.authorize_net.settings.transaction_key.label',
                'required' => true,
            ])
            ->add('clientKey', TextType::class, [
                'label' => 'oro.authorize_net.settings.client_key.label',
                'required' => true,
                'attr' => ['autocomplete' => 'off'],
            ])
            ->add('authNetRequireCVVEntry', CheckboxType::class, [
                'label' => 'oro.authorize_net.settings.require_cvv.label',
                'required' => false,
            ])
            ->add('authNetTestMode', CheckboxType::class, [
                'label' => 'oro.authorize_net.settings.test_mode.label',
                'required' => false,
            ])
            ->add('enabledCIM', CheckboxType::class, [
                'label' => 'oro.authorize_net.settings.enabled_cim.label',
                'tooltip' => 'oro.authorize_net.settings.enabled_cim.tooltip',
                'required' => false
            ])
            ->add('eCheckEnabled', CheckboxType::class, [
                'label' => 'oro.authorize_net.settings.echeck.enabled.label',
                'tooltip' => 'oro.authorize_net.settings.echeck.enabled.tooltip',
                'required' => false
            ])
            ->add('eCheckLabels', LocalizedFallbackValueCollectionType::class, [
                'label' => 'oro.authorize_net.settings.echeck.label',
                'tooltip' => 'oro.authorize_net.settings.label.tooltip',
                'required' => false,
                'entry_options' => ['constraints' => [new Length(['max' => 255])]]
            ])
            ->add('eCheckShortLabels', LocalizedFallbackValueCollectionType::class, [
                'label' => 'oro.authorize_net.settings.echeck.short_label',
                'tooltip' => 'oro.authorize_net.settings.short_label.tooltip',
                'required' => false,
                'entry_options' => ['constraints' => [new Length(['max' => 255])]]
            ])
            ->add('eCheckAccountTypes', ChoiceType::class, [
                'choices' => AuthorizeNetSettings::ECHECK_ACCOUNT_TYPES,
                'choice_label' => function ($accountType) {
                    return $this->translator->trans(
                        sprintf('oro.authorize_net.settings.echeck.account_types.%s', $accountType)
                    );
                },
                'label' => 'oro.authorize_net.settings.echeck.account_types.label',
                'tooltip' => 'oro.authorize_net.settings.echeck.account_types.tooltip',
                'required' => true,
                'multiple' => true
            ])
            ->add('eCheckConfirmationText', TextareaType::class, [
                'required' => false,
                'label' => 'oro.authorize_net.settings.echeck.confirmation_text.label',
                'tooltip' => 'oro.authorize_net.settings.echeck.confirmation_text.tooltip',
                'empty_data' => $this->translator->trans(
                    'oro.authorize_net.settings.echeck.confirmation_text.placeholder'
                ),
                'attr' => [
                    'placeholder' => 'oro.authorize_net.settings.echeck.confirmation_text.placeholder',
                ]
            ])
            ->add('allowHoldTransaction', CheckboxType::class, [
                'label' => 'oro.authorize_net.settings.allow_hold_transaction.label',
                'tooltip' => 'oro.authorize_net.settings.allow_hold_transaction.tooltip',
                'required' => false
            ])
        ;

        $this->transformWithEncodedValue($builder, 'apiLoginId');
        $this->transformWithEncodedValue($builder, 'clientKey');
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => AuthorizeNetSettings::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::BLOCK_PREFIX;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param string $field
     */
    protected function transformWithEncodedValue(FormBuilderInterface $builder, $field)
    {
        $builder->get($field)->addModelTransformer($this->cryptedDataTransformerFactory->create());
    }
}
