<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option\Address;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option\AbstractOption;

/**
 * Option class to represent phoneNumber field (Authorize.Net SDK, Customer Profile)
 */
class PhoneNumber extends AbstractOption
{
    const PHONE_NUMBER = 'phone_number';

    /**
     * {@inheritdoc}
     */
    protected function getName()
    {
        return self::PHONE_NUMBER;
    }

    /**
     * {@inheritdoc}
     */
    protected function getAllowedTypes()
    {
        return 'string';
    }
}
