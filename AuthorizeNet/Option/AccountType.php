<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

/**
 * Option class to represent bankAccount.accountType field (Authorize.Net SDK)
 */
class AccountType extends AbstractOption
{
    public const ACCOUNT_TYPE = 'account_type';

    #[\Override]
    public function getName()
    {
        return self::ACCOUNT_TYPE;
    }

    #[\Override]
    public function getAllowedTypes()
    {
        return 'string';
    }
}
