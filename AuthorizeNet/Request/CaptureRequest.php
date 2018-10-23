<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Request;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

/**
 * Class to represent CreateTransactionRequest with transactionType=priorAuthCaptureTransaction (Authorize.Net API)
 */
class CaptureRequest extends AbstractTransactionRequest
{
    public function getType(): string
    {
        return Option\Transaction::CAPTURE;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureRequestOptions()
    {
        return parent::configureRequestOptions()->addOption(new Option\OriginalTransaction());
    }
}
