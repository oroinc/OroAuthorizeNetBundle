<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

use Oro\Bundle\OrderBundle\Entity\OrderLineItem;

/**
 * Option class to represent lineItems field (Authorize.Net SDK, transaction request)
 */
class LineItems extends AbstractOption
{
    public const NAME = 'line_items';

    /** @return string */
    protected function getName()
    {
        return self::NAME;
    }

    /** {@inheritdoc} */
    public function getAllowedTypes()
    {
        return sprintf('%s[]', OrderLineItem::class);
    }
}
