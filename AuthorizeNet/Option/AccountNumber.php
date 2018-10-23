<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

/**
 * Option class to represent bankAccount.accountNumber field (Authorize.Net SDK)
 */
class AccountNumber extends AbstractOption
{
    const ACCOUNT_NUMBER = 'account_number';

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::ACCOUNT_NUMBER;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllowedTypes()
    {
        return 'string';
    }
}
