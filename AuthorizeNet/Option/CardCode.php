<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

/**
 * Option class to represent creditCard.cardCode field (Authorize.Net SDK)
 */
class CardCode extends AbstractOption
{
    public const NAME = 'card_code';

    /** @return string */
    protected function getName()
    {
        return self::NAME;
    }

    /** {@inheritdoc} */
    public function getAllowedTypes()
    {
        return 'string';
    }
}
