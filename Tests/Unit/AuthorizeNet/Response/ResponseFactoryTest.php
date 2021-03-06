<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Response;

use JMS\Serializer\ArrayTransformerInterface;
use net\authorize\api\contract\v1 as AnetAPI;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Response\AuthorizeNetSDKResponse;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Response\AuthorizeNetSDKTransactionResponse;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Response\ResponseFactory;

class ResponseFactoryTest extends \PHPUnit\Framework\TestCase
{
    /** @var ResponseFactory */
    protected $factory;

    protected function setUp(): void
    {
        /** @var ArrayTransformerInterface|\PHPUnit\Framework\MockObject\MockObject $serializer */
        $serializer = $this->createMock(ArrayTransformerInterface::class);

        $this->factory = new ResponseFactory($serializer);
    }

    /**
     * @dataProvider createResponseDataProvider
     * @param string $apiResponseClass
     * @param $expectedResponseClass
     */
    public function testCreateResponse($apiResponseClass, $expectedResponseClass)
    {
        /** @var AnetAPI\ANetApiResponseType $apiResponse */
        $apiResponse = new $apiResponseClass;
        $this->assertInstanceOf($expectedResponseClass, $this->factory->createResponse($apiResponse));
    }

    /**
     * @return array
     */
    public function createResponseDataProvider()
    {
        return [
            'CreateTransactionResponse' => [
                'apiResponseClass' => AnetAPI\CreateTransactionResponse::class,
                'expectedResponseClass' => AuthorizeNetSDKTransactionResponse::class
            ],
            'CreateCustomerProfileResponse' => [
                'apiResponseClass' => AnetAPI\CreateCustomerProfileResponse::class,
                'expectedResponseClass' => AuthorizeNetSDKResponse::class
            ],
        ];
    }
}
