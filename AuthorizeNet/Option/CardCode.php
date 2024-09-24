<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

/**
 * Option class to represent creditCard.cardCode field (Authorize.Net SDK)
 */
class CardCode extends AbstractOption
{
    public const NAME = 'card_code';

    /** @return string */
    #[\Override]
    protected function getName()
    {
        return self::NAME;
    }

    #[\Override]
    public function getAllowedTypes()
    {
        return 'string';
    }
}
