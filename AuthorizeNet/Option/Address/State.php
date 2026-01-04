<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option\Address;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option\AbstractOption;

/**
 * Option class to represent state field (Authorize.Net SDK, Customer Profile)
 */
class State extends AbstractOption
{
    public const STATE = 'state';

    #[\Override]
    protected function getName()
    {
        return self::STATE;
    }

    #[\Override]
    protected function getAllowedTypes()
    {
        return 'string';
    }
}
