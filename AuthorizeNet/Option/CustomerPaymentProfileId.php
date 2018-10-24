<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

/**
 * Option class to represent customerPaymentProfileId field (Authorize.Net SDK)
 */
class CustomerPaymentProfileId extends AbstractOption
{
    const CUSTOMER_PAYMENT_PROFILE_ID = 'customer_payment_profile_id';

    /**
     * {@inheritdoc}
     */
    protected function getName()
    {
        return self::CUSTOMER_PAYMENT_PROFILE_ID;
    }

    /**
     * {@inheritdoc}
     */
    protected function getAllowedTypes()
    {
        return 'string';
    }
}
