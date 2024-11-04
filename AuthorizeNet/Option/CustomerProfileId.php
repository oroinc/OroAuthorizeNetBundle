<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

/**
 * Option class to represent customerProfileId field (Authorize.Net SDK)
 */
class CustomerProfileId extends AbstractOption
{
    const CUSTOMER_PROFILE_ID = 'customer_profile_id';

    #[\Override]
    protected function getName()
    {
        return self::CUSTOMER_PROFILE_ID;
    }

    #[\Override]
    protected function getAllowedTypes()
    {
        return 'string';
    }
}
