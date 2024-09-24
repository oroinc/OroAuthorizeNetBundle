<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Client\RequestConfigurator;

use net\authorize\api\contract\v1 as AnetAPI;

/**
 * request configurator for deleteCustomerPaymentProfile request
 */
class DeleteCustomerPaymentProfileRequestConfigurator extends GetCustomerPaymentProfileRequestConfigurator
{
    #[\Override]
    public function isApplicable(AnetAPI\ANetApiRequestType $request, array $options)
    {
        return $request instanceof AnetAPI\DeleteCustomerPaymentProfileRequest;
    }
}
