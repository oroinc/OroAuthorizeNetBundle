<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

/**
 * Option class to represent creditCard.cardNumber field (Authorize.Net SDK)
 */
class CardNumber extends AbstractOption
{
    const CARD_NUMBER = 'card_number';

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::CARD_NUMBER;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllowedTypes()
    {
        return 'string';
    }
}
