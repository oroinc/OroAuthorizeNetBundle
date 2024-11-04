<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

/**
 * Option class to represent order.invoiceNumber field (Authorize.Net SDK, transaction request)
 */
class InvoiceNumber extends AbstractOption
{
    public const NAME = 'invoice_number';

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
