<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

/**
 * Option class to represent creditCard.cardNumber field (Authorize.Net SDK)
 */
class CardNumber extends AbstractOption
{
    public const CARD_NUMBER = 'card_number';

    #[\Override]
    public function getName()
    {
        return self::CARD_NUMBER;
    }

    #[\Override]
    public function getAllowedTypes()
    {
        return 'string';
    }
}
