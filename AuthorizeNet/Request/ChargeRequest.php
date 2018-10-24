<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Request;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

/**
 * Class to represent CreateTransactionRequest with transactionType=authCaptureTransaction (Authorize.Net API)
 */
class ChargeRequest extends AbstractDataFieldsAwareRequest
{
    public function getType(): string
    {
        return Option\Transaction::CHARGE;
    }
}
