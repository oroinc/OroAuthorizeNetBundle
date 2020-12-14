<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Client\RequestConfigurator;

use net\authorize\api\contract\v1 as AnetAPI;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

/**
 * request configurator for deleteCustomerProfile request
 */
class DeleteCustomerProfileRequestConfigurator implements RequestConfiguratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function isApplicable(AnetAPI\ANetApiRequestType $request, array $options)
    {
        return $request instanceof AnetAPI\DeleteCustomerProfileRequest;
    }

    /**
     * @param AnetAPI\ANetApiRequestType|AnetAPI\DeleteCustomerProfileRequest $request
     * @param array $options
     */
    public function handle(AnetAPI\ANetApiRequestType $request, array &$options)
    {
        if (array_key_exists(Option\CustomerProfileId::CUSTOMER_PROFILE_ID, $options)) {
            $request->setCustomerProfileId($options[Option\CustomerProfileId::CUSTOMER_PROFILE_ID]);
        }

        // Remove handled options to prevent handling in fallback configurator
        unset(
            $options[Option\CustomerProfileId::CUSTOMER_PROFILE_ID]
        );
    }
}
