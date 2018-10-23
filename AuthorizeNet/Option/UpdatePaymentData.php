<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

/**
 * Option class for updatePaymentData flag (Authorize.Net SDK)
 */
class UpdatePaymentData extends AbstractOption
{
    const UPDATE_PAYMENT_DATA = 'update_payment_data';

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::UPDATE_PAYMENT_DATA;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllowedTypes()
    {
        return 'bool';
    }
}
