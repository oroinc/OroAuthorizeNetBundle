<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

/**
 * Option class to represent bankAccount.nameOnAccount field (Authorize.Net SDK)
 */
class NameOnAccount extends AbstractOption
{
    public const NAME_ON_ACCOUNT = 'name_on_account';

    #[\Override]
    public function getName()
    {
        return self::NAME_ON_ACCOUNT;
    }

    #[\Override]
    public function getAllowedTypes()
    {
        return 'string';
    }
}
