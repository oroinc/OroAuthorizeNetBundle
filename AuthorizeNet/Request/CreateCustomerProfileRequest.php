<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Request;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

/**
 * Class to represent createCustomerProfileRequest (Authorize.Net API)
 */
class CreateCustomerProfileRequest extends AbstractRequest
{
    const REQUEST_TYPE = 'createCustomerProfileRequest';

    #[\Override]
    public function getType(): string
    {
        return self::REQUEST_TYPE;
    }

    #[\Override]
    protected function configureRequestOptions()
    {
        $this
            ->addOption(new Option\MerchantCustomerId())
            ->addOption(new Option\Email());

        return $this;
    }
}
