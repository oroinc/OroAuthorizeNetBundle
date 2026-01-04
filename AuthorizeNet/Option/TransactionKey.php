<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

/**
 * Option class to represent merchantAuthentication::transactionKey field (Authorize.Net SDK)
 */
class TransactionKey extends AbstractOption
{
    public const TRANSACTION_KEY = 'transaction_key';

    #[\Override]
    protected function getName()
    {
        return self::TRANSACTION_KEY;
    }

    #[\Override]
    protected function getAllowedTypes()
    {
        return 'string';
    }
}
