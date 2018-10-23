<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

/**
 * Option class to represent merchantCustomerId field (Authorize.Net SDK)
 */
class MerchantCustomerId extends AbstractOption
{
    const MERCHANT_CUSTOMER_ID = 'merchant_customer_id';

    /**
     * {@inheritdoc}
     */
    protected function getName()
    {
        return self::MERCHANT_CUSTOMER_ID;
    }

    /**
     * {@inheritdoc}
     */
    protected function getAllowedTypes()
    {
        return 'string';
    }
}
