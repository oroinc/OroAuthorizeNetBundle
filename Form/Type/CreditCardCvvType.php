<?php

namespace Oro\Bundle\AuthorizeNetBundle\Form\Type;

use Oro\Bundle\ValidationBundle\Validator\Constraints\Integer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

/**
 * Form type for CVV2 field
 */
class CreditCardCvvType extends AbstractType
{
    const NAME = 'oro_authorize_net_credit_card_cvv';

    /** @var TranslatorInterface */
    private $translator;

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
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'required' => true,
            'label' => 'oro.authorize_net.credit_card.cvv2.label',
            'mapped' => false,
            'constraints' => [
                new Regex([
                    'pattern' => '/^[0-9]+$/',
                    'message' => $this->translator->trans('oro.authorize_net.validator.regex_numeric', [], 'validators')
                ]),
                new NotBlank(),
                new Length(['min' => 3, 'max' => 4])
            ],
            'attr' => [
                'data-card-cvv' => true,
                'placeholder' => false
            ]
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return PasswordType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::NAME;
    }
}
