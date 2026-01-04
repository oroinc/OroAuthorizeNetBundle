<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option\Address;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option\AbstractOption;

/**
 * Option class to represent address field (Authorize.Net SDK, Customer Profile)
 */
class Address extends AbstractOption
{
    public const ADDRESS = 'address';

    #[\Override]
    protected function getName()
    {
        return self::ADDRESS;
    }

    #[\Override]
    protected function getAllowedTypes()
    {
        return 'string';
    }
}
