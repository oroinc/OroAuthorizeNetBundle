<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Request;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

/**
 * Class to represent getCustomerProfileRequest (Authorize.Net API)
 */
class GetCustomerProfileRequest extends AbstractRequest
{
    public const REQUEST_TYPE = 'getCustomerProfileRequest';

    #[\Override]
    public function getType(): string
    {
        return self::REQUEST_TYPE;
    }

    #[\Override]
    protected function configureRequestOptions()
    {
        $this->addOption(new Option\CustomerProfileId());

        return $this;
    }
}
