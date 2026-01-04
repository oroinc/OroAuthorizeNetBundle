<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

/**
 * Option class to represent transactionType field (Authorize.Net SDK, CreateTransactionRequest)
 */
class Transaction extends AbstractOption
{
    public const TRANSACTION_TYPE = 'transaction_type';

    public const AUTHORIZE = 'authOnlyTransaction';
    public const CAPTURE = 'priorAuthCaptureTransaction';
    public const CHARGE = 'authCaptureTransaction';

    #[\Override]
    protected function getName()
    {
        return self::TRANSACTION_TYPE;
    }

    #[\Override]
    protected function getAllowedValues()
    {
        return [
            self::AUTHORIZE,
            self::CAPTURE,
            self::CHARGE
        ];
    }
}
