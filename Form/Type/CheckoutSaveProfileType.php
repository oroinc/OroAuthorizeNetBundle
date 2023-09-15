<?php

namespace Oro\Bundle\AuthorizeNetBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

/**
 * Form type with save profile checkbox
 */
class CheckoutSaveProfileType extends AbstractType
{
    const NAME = 'oro_authorize_net_checkout_save_profile';

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): ?string
    {
        return CheckboxType::class;
    }
}
