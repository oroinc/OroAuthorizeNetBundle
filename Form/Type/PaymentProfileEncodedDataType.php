<?php

namespace Oro\Bundle\AuthorizeNetBundle\Form\Type;

use Oro\Bundle\AuthorizeNetBundle\Model\DTO\PaymentProfileEncodedDataDTO;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for encodedData of payment profile (tokenized credit card data)
 */
class PaymentProfileEncodedDataType extends AbstractType
{
    const NAME = 'oro_authorize_net_payment_profile_encoded_data';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('descriptor', HiddenType::class, [
            'attr' => [
                'data-encoded-descriptor' => true
            ]
        ]);
        $builder->add('value', HiddenType::class, [
            'attr' => [
                'data-encoded-value' => true
            ]
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PaymentProfileEncodedDataDTO::class
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
