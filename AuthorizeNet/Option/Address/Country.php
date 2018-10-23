<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option\Address;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option\AbstractOption;

/**
 * Option class to represent country field (Authorize.Net SDK, Customer Profile)
 */
class Country extends AbstractOption
{
    const COUNTRY = 'country';

    /**
     * {@inheritdoc}
     */
    protected function getName()
    {
        return self::COUNTRY;
    }

    /**
     * {@inheritdoc}
     */
    protected function getAllowedTypes()
    {
        return 'string';
    }
}
