<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Client\RequestConfigurator;

use net\authorize\api\contract\v1 as AnetAPI;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Client\RequestConfigurator as RequestConfigurator;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

class CreateCustomerProfileRequestConfiguratorTest extends AbstractRequestConfiguratorTest
{
    protected function getConfigurator()
    {
        return new RequestConfigurator\CreateCustomerProfileRequestConfigurator();
    }

    public function isApplicableProvider()
    {
        return [
            'supported' => [
                'request' => new AnetAPI\CreateCustomerProfileRequest(),
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
                'request' => new AnetAPI\CreateCustomerProfileRequest(),
                'options' => [
                    Option\MerchantCustomerId::MERCHANT_CUSTOMER_ID => '123',
                    Option\Email::EMAIL => 'example@oroinc.com',
                ],
                'expectedRequest' => (new AnetAPI\CreateCustomerProfileRequest())->setProfile(
                    (new AnetAPI\CustomerProfileType())
                        ->setMerchantCustomerId('123')
                        ->setEmail('example@oroinc.com')
                )
            ],
            'email only' => [
                'request' => new AnetAPI\CreateCustomerProfileRequest(),
                'options' => [
                    Option\Email::EMAIL => 'example@oroinc.com',
                ],
                'expectedRequest' => (new AnetAPI\CreateCustomerProfileRequest())->setProfile(
                    (new AnetAPI\CustomerProfileType())
                        ->setEmail('example@oroinc.com')
                )
            ],
            'merchand customer id only' => [
                'request' => new AnetAPI\CreateCustomerProfileRequest(),
                'options' => [
                    Option\MerchantCustomerId::MERCHANT_CUSTOMER_ID => '123',
                ],
                'expectedRequest' => (new AnetAPI\CreateCustomerProfileRequest())->setProfile(
                    (new AnetAPI\CustomerProfileType())
                        ->setMerchantCustomerId('123')
                )
            ],
            'no options' => [
                'request' => new AnetAPI\CreateCustomerProfileRequest(),
                'options' => [],
                'expectedRequest' => (new AnetAPI\CreateCustomerProfileRequest())
                    ->setProfile(new AnetAPI\CustomerProfileType())
            ],
        ];
    }
}
