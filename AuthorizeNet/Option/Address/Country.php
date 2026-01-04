<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option\Address;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option\AbstractOption;

/**
 * Option class to represent country field (Authorize.Net SDK, Customer Profile)
 */
class Country extends AbstractOption
{
    public const COUNTRY = 'country';

    #[\Override]
    protected function getName()
    {
        return self::COUNTRY;
    }

    #[\Override]
    protected function getAllowedTypes()
    {
        return 'string';
    }
}
