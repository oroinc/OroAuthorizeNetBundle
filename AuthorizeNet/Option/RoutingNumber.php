<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

/**
 * Option class to represent bankAccount.routingNumber field (Authorize.Net SDK)
 */
class RoutingNumber extends AbstractOption
{
    const ROUTING_NUMBER = 'routing_number';

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::ROUTING_NUMBER;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllowedTypes()
    {
        return 'string';
    }
}
