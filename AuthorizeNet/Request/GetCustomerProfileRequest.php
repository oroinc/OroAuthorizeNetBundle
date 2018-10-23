<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Request;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

/**
 * Class to represent getCustomerProfileRequest (Authorize.Net API)
 */
class GetCustomerProfileRequest extends AbstractRequest
{
    const REQUEST_TYPE = 'getCustomerProfileRequest';

    public function getType(): string
    {
        return self::REQUEST_TYPE;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureRequestOptions()
    {
        $this->addOption(new Option\CustomerProfileId());

        return $this;
    }
}
