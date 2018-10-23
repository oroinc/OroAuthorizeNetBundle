<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Client\RequestConfigurator;

use net\authorize\api\contract\v1 as AnetAPI;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option\Address as AddressOption;

/**
 * request configurator for deleteCustomerPaymentProfile request
 */
class DeleteCustomerPaymentProfileRequestConfigurator extends GetCustomerPaymentProfileRequestConfigurator
{
    /**
     * {@inheritdoc}
     */
    public function isApplicable(AnetAPI\ANetApiRequestType $request, array $options)
    {
        return $request instanceof AnetAPI\DeleteCustomerPaymentProfileRequest;
    }
}
