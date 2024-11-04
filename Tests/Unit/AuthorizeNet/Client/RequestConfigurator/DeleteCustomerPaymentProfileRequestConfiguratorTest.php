<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Client\RequestConfigurator;

use net\authorize\api\contract\v1 as AnetAPI;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Client\RequestConfigurator as RequestConfigurator;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Client\RequestConfigurator\RequestConfiguratorInterface;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

class DeleteCustomerPaymentProfileRequestConfiguratorTest extends AbstractRequestConfiguratorTest
{
    #[\Override]
    protected function getConfigurator(): RequestConfiguratorInterface
    {
        return new RequestConfigurator\DeleteCustomerPaymentProfileRequestConfigurator();
    }

    #[\Override]
    public function isApplicableProvider(): array
    {
        return [
            'supported' => [
                'request' => new AnetAPI\DeleteCustomerPaymentProfileRequest(),
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
                'request' => new AnetAPI\DeleteCustomerPaymentProfileRequest(),
                'options' => [
                    Option\CustomerProfileId::CUSTOMER_PROFILE_ID => '123',
                    Option\CustomerPaymentProfileId::CUSTOMER_PAYMENT_PROFILE_ID => '321',
                ],
                'expectedRequest' => (new AnetAPI\DeleteCustomerPaymentProfileRequest())
                    ->setCustomerProfileId('123')
                    ->setCustomerPaymentProfileId('321')
            ],
            'no options' => [
                'request' => new AnetAPI\DeleteCustomerPaymentProfileRequest(),
                'options' => [],
                'expectedRequest' => (new AnetAPI\DeleteCustomerPaymentProfileRequest())
            ],
            'customer profile id only' => [
                'request' => new AnetAPI\DeleteCustomerPaymentProfileRequest(),
                'options' => [
                    Option\CustomerProfileId::CUSTOMER_PROFILE_ID => '123',
                ],
                'expectedRequest' => (new AnetAPI\DeleteCustomerPaymentProfileRequest())
                    ->setCustomerProfileId('123')
            ],
            'customer payment profile id only' => [
                'request' => new AnetAPI\DeleteCustomerPaymentProfileRequest(),
                'options' => [
                    Option\CustomerPaymentProfileId::CUSTOMER_PAYMENT_PROFILE_ID => '321',
                ],
                'expectedRequest' => (new AnetAPI\DeleteCustomerPaymentProfileRequest())
                    ->setCustomerPaymentProfileId('321')
            ],
        ];
    }
}
