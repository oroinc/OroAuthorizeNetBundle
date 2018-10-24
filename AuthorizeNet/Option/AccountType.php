<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

/**
 * Option class to represent bankAccount.accountType field (Authorize.Net SDK)
 */
class AccountType extends AbstractOption
{
    const ACCOUNT_TYPE = 'account_type';

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::ACCOUNT_TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllowedTypes()
    {
        return 'string';
    }
}
