<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Client\RequestConfigurator;

use net\authorize\api\contract\v1 as AnetAPI;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Client\RequestConfigurator;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Client\RequestConfigurator\RequestConfiguratorInterface;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

class GetTransactionDetailsRequestConfiguratorTest extends AbstractRequestConfiguratorTest
{
    /**
     * {@inheritdoc}
     */
    protected function getConfigurator(): RequestConfiguratorInterface
    {
        return new RequestConfigurator\GetTransactionDetailsRequestConfigurator();
    }

    /**
     * {@inheritdoc}
     */
    public function isApplicableProvider(): array
    {
        return [
            'supported' => [
                'request' => new AnetAPI\GetTransactionDetailsRequest(),
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
     * {@inheritdoc}
     */
    public function handleProvider(): array
    {
        return [
            'all options' => [
                'request' => new AnetAPI\GetTransactionDetailsRequest(),
                'options' => [
                    Option\OriginalTransaction::ORIGINAL_TRANSACTION => 1
                ],
                'expectedRequest' => (new AnetAPI\GetTransactionDetailsRequest())
                    ->setTransId(1)
            ],
            'no options' => [
                'request' => new AnetAPI\GetTransactionDetailsRequest(),
                'options' => [],
                'expectedRequest' => new AnetAPI\GetTransactionDetailsRequest()
            ]
        ];
    }
}
