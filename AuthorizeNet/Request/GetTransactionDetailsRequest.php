<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Request;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

/**
 * Class to represent getTransactionDetailsRequest (Authorize.Net API)
 */
class GetTransactionDetailsRequest extends AbstractRequest
{
    public const REQUEST_TYPE = 'getTransactionDetailsRequest';

    #[\Override]
    public function getType(): string
    {
        return self::REQUEST_TYPE;
    }

    #[\Override]
    protected function configureRequestOptions(): self
    {
        $this->addOption(new Option\OriginalTransaction());

        return $this;
    }
}
