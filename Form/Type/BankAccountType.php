<?php

namespace Oro\Bundle\AuthorizeNetBundle\Form\Type;

use Oro\Bundle\AuthorizeNetBundle\Entity\AuthorizeNetSettings;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Bank account (echeck) form
 */
class BankAccountType extends AbstractType
{
    const NAME = 'oro_authorize_net_bank_account';

    /** @var TranslatorInterface */
    protected $translator;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('accountType', ChoiceType::class, [
            'choices' => $options['allowed_account_types'],
            'choice_label' => function ($accountType) {
                return $this->translator->trans(
                    sprintf('oro.authorize_net.settings.echeck.account_types.%s', $accountType)
                );
            },
            'required' => true,
            'label' => 'oro.authorize_net.echeck.account_type.label',
            'attr' => [
                'autocomplete' => 'off',
                'data-account-type' => true,
                'data-sensitive-data' => true,
                'placeholder' => false
            ],
            'constraints' => [
                new NotBlank()
            ]
        ])->add('routingNumber', TextType::class, [
            'required' => true,
            'label' => 'oro.authorize_net.echeck.routing_number.label',
            'attr' => [
                'autocomplete' => 'off',
                'data-routing-number' => true,
                'data-sensitive-data' => true,
                'placeholder' => false
            ],
            'constraints' => [
                new Regex([
                    'pattern' => '/^[0-9]+$/',
                    'message' => $this->translator->trans('oro.authorize_net.validator.regex_numeric', [], 'validators')
                ]),
                new NotBlank(),
                new Length(['min' => '9', 'max' => '9', 'allowEmptyString' => false])
            ]
        ])->add('accountNumber', TextType::class, [
            'required' => true,
            'label' => 'oro.authorize_net.echeck.account_number.label',
            'attr' => [
                'autocomplete' => 'off',
                'data-account-number' => true,
                'data-last-digits-source' => true,
                'data-sensitive-data' => true,
                'placeholder' => false
            ],
            'constraints' => [
                new Regex([
                    'pattern' => '/^[0-9]+$/',
                    'message' => $this->translator->trans('oro.authorize_net.validator.regex_numeric', [], 'validators')
                ]),
                new NotBlank(),
                new Length(['max' => '17'])
            ]
        ])->add('nameOnAccount', TextType::class, [
            'required' => true,
            'label' => 'oro.authorize_net.echeck.name_on_account.label',
            'attr' => [
                'autocomplete' => 'off',
                'data-name-on-account' => true,
                'data-sensitive-data' => true,
                'placeholder' => false
            ],
            'constraints' => [
                new NotBlank(),
                new Length(['max' => '22'])
            ]
        ])->add('bankName', TextType::class, [
            'required' => false,
            'label' => 'oro.authorize_net.echeck.bank_name.label',
            'attr' => [
                'autocomplete' => 'off',
                'data-bank-name' => true,
                'data-sensitive-data' => true,
                'placeholder' => false
            ],
            'constraints' => [
                new Length(['max' => '50'])
            ]
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['confirmation_text'] = $options['confirmation_text'];
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'label' => 'oro.authorize_net.methods.echeck.label',
            'csrf_protection' => false,
            'confirmation_text' => '',
            'allowed_account_types' => AuthorizeNetSettings::ECHECK_ACCOUNT_TYPES
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::NAME;
    }
}
