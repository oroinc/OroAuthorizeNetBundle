<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Client\RequestConfigurator;

use net\authorize\api\contract\v1 as AnetAPI;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

/**
 * Request Configurator for configuring merchantAuthentication field
 */
class MerchantAuthenticationRequestConfigurator implements RequestConfiguratorInterface
{
    /**
     * @param AnetAPI\ANetApiRequestType $request
     * @param array $options
     * @return bool
     */
    public function isApplicable(AnetAPI\ANetApiRequestType $request, array $options)
    {
        return array_key_exists(Option\ApiLoginId::API_LOGIN_ID, $options)
            && array_key_exists(Option\TransactionKey::TRANSACTION_KEY, $options);
    }

    /**
     * @param AnetAPI\ANetApiRequestType $request
     * @param array $options
     */
    public function handle(AnetAPI\ANetApiRequestType $request, array &$options)
    {
        $request->setMerchantAuthentication($this->getMerchantAuthenticationType($options));

        // Remove handled options to prevent handling in fallback configurator
        unset($options[Option\ApiLoginId::API_LOGIN_ID], $options[Option\TransactionKey::TRANSACTION_KEY]);
    }

    /**
     * @param array $options
     * @return AnetAPI\MerchantAuthenticationType
     */
    protected function getMerchantAuthenticationType(array $options)
    {
        $merchantAuthenticationType = new AnetAPI\MerchantAuthenticationType();
        $merchantAuthenticationType
            ->setName($options[Option\ApiLoginId::API_LOGIN_ID])
            ->setTransactionKey($options[Option\TransactionKey::TRANSACTION_KEY]);

        return $merchantAuthenticationType;
    }
}
