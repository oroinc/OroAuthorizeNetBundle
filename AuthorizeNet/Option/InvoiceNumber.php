<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

/**
 * Option class to represent order.invoiceNumber field (Authorize.Net SDK, transaction request)
 */
class InvoiceNumber extends AbstractOption
{
    public const NAME = 'invoice_number';

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
