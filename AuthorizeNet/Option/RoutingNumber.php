<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

/**
 * Option class to represent bankAccount.routingNumber field (Authorize.Net SDK)
 */
class RoutingNumber extends AbstractOption
{
    public const ROUTING_NUMBER = 'routing_number';

    #[\Override]
    public function getName()
    {
        return self::ROUTING_NUMBER;
    }

    #[\Override]
    public function getAllowedTypes()
    {
        return 'string';
    }
}
