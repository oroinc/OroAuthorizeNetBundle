<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option\Address;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option\AbstractOption;

/**
 * Option class to represent phoneNumber field (Authorize.Net SDK, Customer Profile)
 */
class PhoneNumber extends AbstractOption
{
    public const PHONE_NUMBER = 'phone_number';

    #[\Override]
    protected function getName()
    {
        return self::PHONE_NUMBER;
    }

    #[\Override]
    protected function getAllowedTypes()
    {
        return 'string';
    }
}
