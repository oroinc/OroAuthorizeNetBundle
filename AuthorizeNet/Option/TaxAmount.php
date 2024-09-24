<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

/**
 * Option class to represent tax.amount field (Authorize.Net SDK, transaction request)
 */
class TaxAmount extends AbstractOption
{
    public const NAME = 'tax_amount';

    /** @return string */
    #[\Override]
    protected function getName()
    {
        return self::NAME;
    }

    #[\Override]
    public function getAllowedTypes()
    {
        return ['float', 'integer'];
    }
}
