<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

/**
 * Option class to represent originalTransaction field (Authorize.Net SDK, CreateTransactionRequest)
 */
class OriginalTransaction extends AbstractOption
{
    const ORIGINAL_TRANSACTION = 'original_transaction';

    #[\Override]
    protected function getName()
    {
        return self::ORIGINAL_TRANSACTION;
    }

    #[\Override]
    protected function getAllowedTypes()
    {
        return ['integer', 'string'];
    }
}
