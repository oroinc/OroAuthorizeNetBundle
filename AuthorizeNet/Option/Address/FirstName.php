<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option\Address;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option\AbstractOption;

/**
 * Option class to represent country field (Authorize.Net SDK, Customer Profile)
 */
class FirstName extends AbstractOption
{
    public const FIRST_NAME = 'first_name';

    #[\Override]
    protected function getName()
    {
        return self::FIRST_NAME;
    }

    #[\Override]
    protected function getAllowedTypes()
    {
        return 'string';
    }
}
