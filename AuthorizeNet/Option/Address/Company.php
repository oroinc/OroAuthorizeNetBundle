<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option\Address;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option\AbstractOption;

/**
 * Option class to represent company field (Authorize.Net SDK, Customer Profile)
 */
class Company extends AbstractOption
{
    public const COMPANY = 'company';

    #[\Override]
    protected function getName()
    {
        return self::COMPANY;
    }

    #[\Override]
    protected function getAllowedTypes()
    {
        return 'string';
    }
}
