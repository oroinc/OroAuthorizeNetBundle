<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Request;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

/**
 * Class to represent createCustomerPaymentProfileRequest (Authorize.Net API)
 */
class CreateCustomerPaymentProfileRequest extends AbstractRequest
{
    const REQUEST_TYPE = 'createCustomerPaymentProfileRequest';

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
            ->addOption(new Option\CustomerAddress())
            ->addOption(new Option\DataDescriptor())
            ->addOption(new Option\DataValue())
            ->addOption(new Option\IsDefault())
            ->addOption(new Option\ValidationMode());

        return $this;
    }
}
