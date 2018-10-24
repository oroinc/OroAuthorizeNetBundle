<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Client\RequestConfigurator;

use net\authorize\api\contract\v1 as AnetAPI;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Client\RequestConfigurator as RequestConfigurator;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

class GetCustomerPaymentProfileRequestConfiguratorTest extends AbstractRequestConfiguratorTest
{
    protected function getConfigurator()
    {
        return new RequestConfigurator\GetCustomerPaymentProfileRequestConfigurator();
    }

    protected function getPriority()
    {
        return 0;
    }

    public function isApplicableProvider()
    {
        return [
            'supported' => [
                'request' => new AnetAPI\GetCustomerPaymentProfileRequest(),
                'options' => [],
                'expectedResult' => true
            ],
            'unsupported' => [
                'request' => new AnetAPI\ANetApiRequestType(),
                'options' => [],
                'expectedResult' => false
            ]
        ];
    }
    /**
     * @return array
     */
    public function handleProvider()
    {
        return [
            'all options' => [
                'request' => new AnetAPI\GetCustomerPaymentProfileRequest(),
                'options' => [
                    Option\CustomerProfileId::CUSTOMER_PROFILE_ID => '123',
                    Option\CustomerPaymentProfileId::CUSTOMER_PAYMENT_PROFILE_ID => '321',
                ],
                'expectedRequest' => (new AnetAPI\GetCustomerPaymentProfileRequest())
                    ->setCustomerProfileId('123')
                    ->setCustomerPaymentProfileId('321')
            ],
            'no options' => [
                'request' => new AnetAPI\GetCustomerPaymentProfileRequest(),
                'options' => [],
                'expectedRequest' => (new AnetAPI\GetCustomerPaymentProfileRequest())
            ],
            'customer profile id only' => [
                'request' => new AnetAPI\GetCustomerPaymentProfileRequest(),
                'options' => [
                    Option\CustomerProfileId::CUSTOMER_PROFILE_ID => '123',
                ],
                'expectedRequest' => (new AnetAPI\GetCustomerPaymentProfileRequest())
                    ->setCustomerProfileId('123')
            ],
            'customer payment profile id only' => [
                'request' => new AnetAPI\GetCustomerPaymentProfileRequest(),
                'options' => [
                    Option\CustomerPaymentProfileId::CUSTOMER_PAYMENT_PROFILE_ID => '321',
                ],
                'expectedRequest' => (new AnetAPI\GetCustomerPaymentProfileRequest())
                    ->setCustomerPaymentProfileId('321')
            ],
        ];
    }
}
