<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

/**
 * Option class to represent creditCard.expirationDate field (Authorize.Net SDK)
 */
class ExpirationDate extends AbstractOption
{
    const EXPIRATION_DATE = 'expiration_date';

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::EXPIRATION_DATE;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllowedTypes()
    {
        return 'string';
    }
}
