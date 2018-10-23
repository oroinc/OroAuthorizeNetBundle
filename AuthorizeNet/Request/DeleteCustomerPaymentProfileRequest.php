<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Request;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

/**
 * Class to represent deleteCustomerPaymentProfileRequest (Authorize.Net API)
 */
class DeleteCustomerPaymentProfileRequest extends AbstractRequest
{
    const REQUEST_TYPE = 'deleteCustomerPaymentProfileRequest';

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
        $this->addOption(new Option\CustomerPaymentProfileId());

        return $this;
    }
}
