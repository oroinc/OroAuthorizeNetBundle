<?php

namespace Oro\Bundle\AuthorizeNetBundle\Form\Type;

use Oro\Bundle\AuthorizeNetBundle\Entity\CustomerPaymentProfile;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for extended payment method form (payment profile + credit card)
 */
class CheckoutCredicardProfileType extends AbstractType
{
    const NAME = 'oro_authorize_net_checkout_creditcard_profile';

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['requireCvvEntryEnabled']) {
            $builder->add('profileCVV', CreditCardCvvType::class, [
                'block_name' => 'profile_cvv',
                'attr' => [
                    'data-profile-cvv-field' => true,
                    'placeholder' => false
                ]
            ]);
        }

        $builder->add('paymentData', CreditCardType::class, [
            'label' => false,
            'requireCvvEntryEnabled' => $options['requireCvvEntryEnabled'],
            'allowedCreditCards' => $options['allowedCreditCards']
        ]);
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'label' => false,
            'csrf_protection' => false,
            'requireCvvEntryEnabled' => true,
            'allowedCreditCards' => [],
            'profile_type' => CustomerPaymentProfile::TYPE_CREDITCARD
        ]);
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return self::NAME;
    }

    #[\Override]
    public function getParent(): ?string
    {
        return CheckoutPaymentProfileType::class;
    }
}
