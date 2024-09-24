<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

/**
 * Option class to represent customerIP field (Authorize.Net SDK)
 */
class CustomerIp extends AbstractOption
{
    public const NAME = 'customer_ip';

    #[\Override]
    protected function getName()
    {
        return self::NAME;
    }

    #[\Override]
    protected function getAllowedTypes()
    {
        return 'string';
    }
}
