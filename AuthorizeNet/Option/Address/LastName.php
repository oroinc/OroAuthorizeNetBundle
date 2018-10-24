<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option\Address;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option\AbstractOption;

/**
 * Option class to represent lastName field (Authorize.Net SDK, Customer Profile)
 */
class LastName extends AbstractOption
{
    const LAST_NAME = 'last_name';

    /**
     * {@inheritdoc}
     */
    protected function getName()
    {
        return self::LAST_NAME;
    }

    /**
     * {@inheritdoc}
     */
    protected function getAllowedTypes()
    {
        return 'string';
    }
}
