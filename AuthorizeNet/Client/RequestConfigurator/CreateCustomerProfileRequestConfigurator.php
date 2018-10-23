<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Client\RequestConfigurator;

use net\authorize\api\contract\v1 as AnetAPI;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option\Address as AddressOption;

/**
 * request configurator for createCustomerProfile request
 */
class CreateCustomerProfileRequestConfigurator implements RequestConfiguratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function getPriority()
    {
        return 0;
    }

    /**
     * {@inheritdoc}
     */
    public function isApplicable(AnetAPI\ANetApiRequestType $request, array $options)
    {
        return $request instanceof AnetAPI\CreateCustomerProfileRequest;
    }

    /**
     * @param AnetAPI\ANetApiRequestType|AnetAPI\CreateCustomerProfileRequest $request
     * @param array $options
     */
    public function handle(AnetAPI\ANetApiRequestType $request, array &$options)
    {
        $profile = new AnetAPI\CustomerProfileType();

        if (array_key_exists(Option\MerchantCustomerId::MERCHANT_CUSTOMER_ID, $options)) {
            $profile->setMerchantCustomerId($options[Option\MerchantCustomerId::MERCHANT_CUSTOMER_ID]);
        }

        if (array_key_exists(Option\Email::EMAIL, $options)) {
            $profile->setEmail($options[Option\Email::EMAIL]);
        }

        $request->setProfile($profile);

        // Remove handled options to prevent handling in fallback configurator
        unset(
            $options[Option\MerchantCustomerId::MERCHANT_CUSTOMER_ID],
            $options[Option\Email::EMAIL]
        );
    }
}
