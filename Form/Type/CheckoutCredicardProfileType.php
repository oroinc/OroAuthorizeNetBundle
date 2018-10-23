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

    /**
     * {@inheritdoc}
     */
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

    /**
     * {@inheritdoc}
     */
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

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return CheckoutPaymentProfileType::class;
    }
}
