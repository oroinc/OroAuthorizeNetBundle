<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Response;

use JMS\Serializer\ArrayTransformerInterface;
use net\authorize\api\contract\v1 as AnetAPI;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Response\AuthorizeNetSDKResponse;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Response\AuthorizeNetSDKTransactionResponse;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Response\ResponseFactory;

class ResponseFactoryTest extends \PHPUnit\Framework\TestCase
{
    public function testCreateResponseForCreateTransactionResponse()
    {
        $apiResponse = new AnetAPI\CreateTransactionResponse();

        $factory = new ResponseFactory();
        $response = $factory->createResponse($apiResponse);

        $this->assertInstanceOf(AuthorizeNetSDKTransactionResponse::class, $response);
    }

    public function testCreateResponseForAnotherApiResponse()
    {
        $apiResponse = new AnetAPI\CreateCustomerProfileResponse();

        $factory = new ResponseFactory();
        $response = $factory->createResponse($apiResponse);

        $this->assertInstanceOf(AuthorizeNetSDKResponse::class, $response);
    }

    public function testShouldBePossibleToGetApiResponseDataUsingDefaultSerializer()
    {
        $apiResponse = new AnetAPI\CreateCustomerProfileResponse();
        $apiResponse->setRefId('test_ref_id');

        $factory = new ResponseFactory();
        $response = $factory->createResponse($apiResponse);

        self::assertInstanceOf(AuthorizeNetSDKResponse::class, $response);
        self::assertEquals(['ref_id' => 'test_ref_id'], $response->getData());
    }

    public function testShouldBePossibleToProvideCustomSerializer()
    {
        $apiResponse = new AnetAPI\CreateCustomerProfileResponse();
        $serializedApiResponse = ['key' => 'val'];

        $serializer = $this->createMock(ArrayTransformerInterface::class);
        $serializer->expects(self::once())
            ->method('toArray')
            ->with(self::identicalTo($apiResponse))
            ->willReturn($serializedApiResponse);

        $factory = new ResponseFactory($serializer);
        $response = $factory->createResponse($apiResponse);

        self::assertInstanceOf(AuthorizeNetSDKResponse::class, $response);
        self::assertEquals($serializedApiResponse, $response->getData());
    }
}
