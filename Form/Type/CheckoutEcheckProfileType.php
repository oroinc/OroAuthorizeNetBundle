<?php

namespace Oro\Bundle\AuthorizeNetBundle\Form\Type;

use Oro\Bundle\AuthorizeNetBundle\Entity\AuthorizeNetSettings;
use Oro\Bundle\AuthorizeNetBundle\Entity\CustomerPaymentProfile;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for extended payment method form (payment profile + bank account)
 */
class CheckoutEcheckProfileType extends AbstractType
{
    public const NAME = 'oro_authorize_net_checkout_echeck_profile';

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('paymentData', BankAccountType::class, [
            'label' => false,
            'confirmation_text' => $options['confirmation_text'],
            'allowed_account_types' => $options['allowed_account_types']
        ]);
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'label' => false,
            'csrf_protection' => false,
            'profile_type' => CustomerPaymentProfile::TYPE_ECHECK,
            'confirmation_text' => '',
            'allowed_account_types' => AuthorizeNetSettings::ECHECK_ACCOUNT_TYPES
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
