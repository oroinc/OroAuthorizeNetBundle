<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

/**
 * Option class to represent merchantAuthentication::transactionKey field (Authorize.Net SDK)
 */
class TransactionKey extends AbstractOption
{
    const TRANSACTION_KEY = 'transaction_key';

    /**
     * {@inheritdoc}
     */
    protected function getName()
    {
        return self::TRANSACTION_KEY;
    }

    /**
     * {@inheritdoc}
     */
    protected function getAllowedTypes()
    {
        return 'string';
    }
}
