<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Client\RequestConfigurator;

use net\authorize\api\contract\v1 as AnetAPI;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Client\RequestConfigurator;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Client\RequestConfigurator\RequestConfiguratorInterface;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

class GetCustomerProfileRequestConfiguratorTest extends AbstractRequestConfiguratorTest
{
    #[\Override]
    protected function getConfigurator(): RequestConfiguratorInterface
    {
        return new RequestConfigurator\GetCustomerProfileRequestConfigurator();
    }

    #[\Override]
    public function isApplicableProvider(): array
    {
        return [
            'supported' => [
                'request' => new AnetAPI\GetCustomerProfileRequest(),
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

    #[\Override]
    public function handleProvider(): array
    {
        return [
            'all options' => [
                'request' => new AnetAPI\GetCustomerProfileRequest(),
                'options' => [
                    Option\CustomerProfileId::CUSTOMER_PROFILE_ID => '123'
                ],
                'expectedRequest' => (new AnetAPI\GetCustomerProfileRequest())
                    ->setCustomerProfileId('123')
            ],
            'no options' => [
                'request' => new AnetAPI\GetCustomerProfileRequest(),
                'options' => [],
                'expectedRequest' => new AnetAPI\GetCustomerProfileRequest()
            ]
        ];
    }
}
