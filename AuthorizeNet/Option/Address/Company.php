<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option\Address;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option\AbstractOption;

/**
 * Option class to represent company field (Authorize.Net SDK, Customer Profile)
 */
class Company extends AbstractOption
{
    const COMPANY = 'company';

    /**
     * {@inheritdoc}
     */
    protected function getName()
    {
        return self::COMPANY;
    }

    /**
     * {@inheritdoc}
     */
    protected function getAllowedTypes()
    {
        return 'string';
    }
}
