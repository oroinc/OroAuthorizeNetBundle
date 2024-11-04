<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Request;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

/**
 * Class to represent deleteCustomerProfileRequest (Authorize.Net API)
 */
class DeleteCustomerProfileRequest extends AbstractRequest
{
    const REQUEST_TYPE = 'deleteCustomerProfileRequest';

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
