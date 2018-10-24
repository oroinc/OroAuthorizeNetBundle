<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option\Address;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option\AbstractOption;

/**
 * Option class to represent city field (Authorize.Net SDK, Customer Profile)
 */
class City extends AbstractOption
{
    const CITY = 'city';

    /**
     * {@inheritdoc}
     */
    protected function getName()
    {
        return self::CITY;
    }

    /**
     * {@inheritdoc}
     */
    protected function getAllowedTypes()
    {
        return 'string';
    }
}
