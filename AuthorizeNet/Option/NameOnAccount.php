<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

/**
 * Option class to represent bankAccount.nameOnAccount field (Authorize.Net SDK)
 */
class NameOnAccount extends AbstractOption
{
    const NAME_ON_ACCOUNT = 'name_on_account';

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME_ON_ACCOUNT;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllowedTypes()
    {
        return 'string';
    }
}
