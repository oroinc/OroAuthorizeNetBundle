<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option\Address;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option\AbstractOption;

/**
 * Option class to represent zip field (Authorize.Net SDK, Customer Profile)
 */
class Zip extends AbstractOption
{
    const ZIP = 'zip';

    /**
     * {@inheritdoc}
     */
    protected function getName()
    {
        return self::ZIP;
    }

    /**
     * {@inheritdoc}
     */
    protected function getAllowedTypes()
    {
        return 'string';
    }
}
