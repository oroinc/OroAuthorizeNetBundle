<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

/**
 * Option class to represent merchantCustomerId field (Authorize.Net SDK)
 */
class MerchantCustomerId extends AbstractOption
{
    const MERCHANT_CUSTOMER_ID = 'merchant_customer_id';

    #[\Override]
    protected function getName()
    {
        return self::MERCHANT_CUSTOMER_ID;
    }

    #[\Override]
    protected function getAllowedTypes()
    {
        return 'string';
    }
}
