<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Request;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

/**
 * Class to represent updateCustomerPaymentProfileRequest (Authorize.Net API)
 */
class UpdateCustomerPaymentProfileRequest extends AbstractRequest
{
    const REQUEST_TYPE = 'updateCustomerPaymentProfileRequest';

    #[\Override]
    public function getType(): string
    {
        return self::REQUEST_TYPE;
    }

    #[\Override]
    protected function configureRequestOptions()
    {
        $this
            ->addOption(new Option\CustomerProfileId())
            ->addOption(new Option\CustomerPaymentProfileId())
            ->addOption(new Option\CustomerAddress())
            ->addOption(new Option\PaymentData())
            ->addOption(new Option\IsDefault())
            ->addOption(new Option\ValidationMode($isRequired = false));

        return $this;
    }
}
