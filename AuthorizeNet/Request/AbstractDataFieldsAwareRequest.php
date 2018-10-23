<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Request;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

/**
 * Request to represent common flow for AUTHORIZE and CHARGE requests
 * "createTransactionRequest" (Authorize.Net API)
 * which have same structure (authOnlyTransaction, authCaptureTransaction)
 * but differs from CAPTURE request (priorAuthCaptureTransaction)
 * most of resolving logic encapsulated in ChargeData dependent option
 */
abstract class AbstractDataFieldsAwareRequest extends AbstractTransactionRequest
{
    /**
     * {@inheritdoc}
     */
    protected function configureRequestOptions()
    {
        return parent::configureRequestOptions()->addOption(new Option\ChargeData());
    }
}
