<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

/**
 * Option class to represent email field (Authorize.Net SDK)
 */
class Email extends AbstractOption
{
    const EMAIL = 'email';

    /**
     * {@inheritdoc}
     */
    protected function getName()
    {
        return self::EMAIL;
    }

    /**
     * {@inheritdoc}
     */
    protected function getAllowedTypes()
    {
        return 'string';
    }
}
