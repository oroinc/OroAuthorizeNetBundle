<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

/**
 * Option class to represent transactionType field (Authorize.Net SDK, CreateTransactionRequest)
 */
class Transaction extends AbstractOption
{
    const TRANSACTION_TYPE = 'transaction_type';

    const AUTHORIZE = 'authOnlyTransaction';
    const CAPTURE = 'priorAuthCaptureTransaction';
    const CHARGE = 'authCaptureTransaction';

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
