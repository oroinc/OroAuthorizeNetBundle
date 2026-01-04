<?php

namespace Oro\Bundle\AuthorizeNetBundle\Form\Type;

use Oro\Bundle\ValidationBundle\Validator\Constraints\Integer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Credit card form
 */
class CreditCardType extends AbstractType
{
    public const NAME = 'oro_authorize_net_credit_card';

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'ACCT',
            TextType::class,
            [
                'required' => true,
                'label' => 'oro.authorize_net.credit_card.card_number.label',
                'mapped' => false,
                'attr' => [
                    'data-validation' => [
                        'credit-card-number' => [
                            'message' => 'oro.payment.validation.credit_card',
                            'payload' => null
                        ],
                        'credit-card-type' => [
                            'message' => 'oro.payment.validation.credit_card_type',
                            'payload' => null,
                            'allowedCreditCards' => $options['allowedCreditCards']
                        ]
                    ],
                    'data-sensitive-data' => true,
                    'data-card-number' => true,
                    'data-last-digits-source' => true,
                    'autocomplete' => 'off',
                    'data-gateway' => true,
                    'placeholder' => false
                ],
                'constraints' => [
                    new Integer(),
                    new NotBlank(),
                    new Length(['min' => '12', 'max' => '19'])
                ]
            ]
        )->add(
            'expirationDate',
            CreditCardExpirationDateType::class,
            [
                'required' => true,
                'label' => 'oro.authorize_net.credit_card.expiration_date.label',
                'mapped' => false,
                'placeholder' => [
                    'year' => 'oro.authorize_net.credit_card.expiration_date.year',
                    'month' => 'oro.authorize_net.credit_card.expiration_date.month',
                ],
                'attr' => [
                    'data-expiration-date' => true,
                    'data-validation-ignore-onblur' => true,
                ]
            ]
        );

        if ($options['requireCvvEntryEnabled']) {
            $builder->add('CVV2', CreditCardCvvType::class, [
                'attr' => [
                    'data-card-cvv' => true,
                    'data-sensitive-data' => true,
                    'placeholder' => false,
                ]
            ]);
        }
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'label' => 'oro.authorize_net.methods.credit_card.label',
            'csrf_protection' => false,
            'requireCvvEntryEnabled' => true,
            'allowedCreditCards' => []
        ]);
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return self::NAME;
    }
}
