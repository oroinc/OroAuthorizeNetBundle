<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option\Address;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option\AbstractOption;

/**
 * Option class to represent faxNumber field (Authorize.Net SDK, Customer Profile)
 */
class FaxNumber extends AbstractOption
{
    public const FAX_NUMBER = 'fax_number';

    #[\Override]
    public function getName()
    {
        return self::FAX_NUMBER;
    }

    #[\Override]
    public function getAllowedTypes()
    {
        return 'string';
    }
}
