<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Client\RequestConfigurator;

use net\authorize\api\contract\v1 as AnetAPI;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Client\RequestConfigurator as RequestConfigurator;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Client\RequestConfigurator\RequestConfiguratorInterface;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

class DeleteCustomerProfileRequestConfiguratorTest extends AbstractRequestConfiguratorTest
{
    #[\Override]
    protected function getConfigurator(): RequestConfiguratorInterface
    {
        return new RequestConfigurator\DeleteCustomerProfileRequestConfigurator();
    }

    #[\Override]
    public function isApplicableProvider(): array
    {
        return [
            'supported' => [
                'request' => new AnetAPI\DeleteCustomerProfileRequest(),
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
                'request' => new AnetAPI\DeleteCustomerProfileRequest(),
                'options' => [
                    Option\CustomerProfileId::CUSTOMER_PROFILE_ID => '123'
                ],
                'expectedRequest' => (new AnetAPI\DeleteCustomerProfileRequest())->setCustomerProfileId('123')
            ],
            'no options' => [
                'request' => new AnetAPI\DeleteCustomerProfileRequest(),
                'options' => [],
                'expectedRequest' => new AnetAPI\DeleteCustomerProfileRequest()
            ],
        ];
    }
}
