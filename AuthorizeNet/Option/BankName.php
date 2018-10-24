<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

/**
 * Option class to represent bankAccount.bankName field (Authorize.Net SDK)
 */
class BankName extends AbstractOption
{
    const BANK_NAME = 'bank_name';

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::BANK_NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllowedTypes()
    {
        return 'string';
    }
}
