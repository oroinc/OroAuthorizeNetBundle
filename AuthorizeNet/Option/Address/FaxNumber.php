<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option\Address;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option\AbstractOption;

/**
 * Option class to represent faxNumber field (Authorize.Net SDK, Customer Profile)
 */
class FaxNumber extends AbstractOption
{
    const FAX_NUMBER = 'fax_number';

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::FAX_NUMBER;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllowedTypes()
    {
        return 'string';
    }
}
