<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Client\RequestConfigurator;

use net\authorize\api\contract\v1 as AnetAPI;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

/**
 * request configurator for getCustomerPaymentProfile request
 */
class GetCustomerPaymentProfileRequestConfigurator implements RequestConfiguratorInterface
{
    #[\Override]
    public function isApplicable(AnetAPI\ANetApiRequestType $request, array $options)
    {
        return $request instanceof AnetAPI\GetCustomerPaymentProfileRequest;
    }

    /**
     * @param AnetAPI\ANetApiRequestType|AnetAPI\GetCustomerPaymentProfileRequest $request
     * @param array $options
     */
    #[\Override]
    public function handle(AnetAPI\ANetApiRequestType $request, array &$options)
    {
        if (array_key_exists(Option\CustomerProfileId::CUSTOMER_PROFILE_ID, $options)) {
            $request->setCustomerProfileId($options[Option\CustomerProfileId::CUSTOMER_PROFILE_ID]);
        }

        if (array_key_exists(Option\CustomerPaymentProfileId::CUSTOMER_PAYMENT_PROFILE_ID, $options)) {
            $request
                ->setCustomerPaymentProfileId($options[Option\CustomerPaymentProfileId::CUSTOMER_PAYMENT_PROFILE_ID]);
        }

        // Remove handled options to prevent handling in fallback configurator
        unset(
            $options[Option\CustomerProfileId::CUSTOMER_PROFILE_ID],
            $options[Option\CustomerPaymentProfileId::CUSTOMER_PAYMENT_PROFILE_ID]
        );
    }
}
