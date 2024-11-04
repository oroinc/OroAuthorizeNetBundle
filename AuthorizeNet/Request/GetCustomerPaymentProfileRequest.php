<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Request;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

/**
 * Class to represent getCustomerPaymentProfileRequest (Authorize.Net API)
 */
class GetCustomerPaymentProfileRequest extends AbstractRequest
{
    const REQUEST_TYPE = 'getCustomerPaymentProfileRequest';

    #[\Override]
    public function getType(): string
    {
        return self::REQUEST_TYPE;
    }

    #[\Override]
    protected function configureRequestOptions()
    {
        $this->addOption(new Option\CustomerProfileId());
        $this->addOption(new Option\CustomerPaymentProfileId());

        return $this;
    }
}
