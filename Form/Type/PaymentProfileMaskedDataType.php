<?php

namespace Oro\Bundle\AuthorizeNetBundle\Form\Type;

use Oro\Bundle\AuthorizeNetBundle\Model\DTO\PaymentProfileMaskedDataDTO;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for masked bank account data
 */
class PaymentProfileMaskedDataType extends AbstractType
{
    public const NAME = 'oro_authorize_net_payment_profile_masked_data';

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('accountNumber', HiddenType::class);
        $builder->add('routingNumber', HiddenType::class);
        $builder->add('nameOnAccount', HiddenType::class);
        $builder->add('accountType', HiddenType::class);
        $builder->add('bankName', HiddenType::class);
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PaymentProfileMaskedDataDTO::class
        ]);
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return self::NAME;
    }
}
