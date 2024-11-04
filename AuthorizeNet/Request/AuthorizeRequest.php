<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Request;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

/**
 * Class to represent CreateTransactionRequest with transactionType=authOnlyTransaction (Authorize.Net API)
 */
class AuthorizeRequest extends AbstractDataFieldsAwareRequest
{
    #[\Override]
    public function getType(): string
    {
        return Option\Transaction::AUTHORIZE;
    }
}
