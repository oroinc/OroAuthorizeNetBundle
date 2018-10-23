<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option\Address;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option\AbstractOption;

/**
 * Option class to represent address field (Authorize.Net SDK, Customer Profile)
 */
class Address extends AbstractOption
{
    const ADDRESS = 'address';

    /**
     * {@inheritdoc}
     */
    protected function getName()
    {
        return self::ADDRESS;
    }

    /**
     * {@inheritdoc}
     */
    protected function getAllowedTypes()
    {
        return 'string';
    }
}
