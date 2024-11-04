<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

/**
 * Option class to represent email field (Authorize.Net SDK)
 */
class Email extends AbstractOption
{
    const EMAIL = 'email';

    #[\Override]
    protected function getName()
    {
        return self::EMAIL;
    }

    #[\Override]
    protected function getAllowedTypes()
    {
        return 'string';
    }
}
