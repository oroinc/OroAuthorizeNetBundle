<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Client;

use JMS\Serializer\Serializer;
use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Client\AuthorizeNetSDKClient;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Client\Factory\AnetSDKRequestFactoryInterface;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Response\AuthorizeNetSDKResponse;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Response\ResponseFactory;
use Psr\Log\LoggerInterface;

class AuthorizeNetSDKClientTest extends \PHPUnit\Framework\TestCase
{
    const HOST_ADDRESS = 'http://example.local/api';

    /** @var ResponseFactory */
    protected $responseFactory;

    /** @var AnetSDKRequestFactoryInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $requestFactory;

    /** @var AuthorizeNetSDKClient */
    protected $client;

    protected function setUp()
    {
        $this->requestFactory = $this->createMock(AnetSDKRequestFactoryInterface::class);
        /** @var Serializer|\PHPUnit\Framework\MockObject\MockObject $serializer */
        $serializer = $this->createMock(Serializer::class);
        $logger = $this->createMock(LoggerInterface::class);
        $this->responseFactory = new ResponseFactory($serializer);
        $this->client = new AuthorizeNetSDKClient($this->requestFactory, $this->responseFactory, $logger);
    }

    /**
     * @dataProvider sendDataProvider
     * @param string $requestType
     * @param string $apiRequestClass
     * @param string $apiControllerClass
     * @param string $apiResponseClass
     */
    public function testSend($requestType, $apiRequestClass, $apiControllerClass, $apiResponseClass)
    {
        $requestOptions = $this->getRequiredOptionsData();

        $request = $this->createMock($apiRequestClass);
        $this->requestFactory->expects($this->once())
            ->method('createRequest')
            ->with($requestType, $requestOptions)
            ->willReturn($request);

        $transactionResponse = new $apiResponseClass;
        $controller = $this->createMock($apiControllerClass);
        $controller->expects($this->once())->method('executeWithApiResponse')
            ->with(self::HOST_ADDRESS)
            ->willReturn($transactionResponse);

        $this->requestFactory->expects($this->once())
            ->method('createController')
            ->with($request)
            ->willReturn($controller);

        $response = $this->client->send(self::HOST_ADDRESS, $requestType, $requestOptions);
        $this->assertInstanceOf(AuthorizeNetSDKResponse::class, $response);
    }

    /**
     * @return array
     */
    public function sendDataProvider()
    {
        return [
            'charge type' => [
                'requestType' => Option\Transaction::CHARGE,
                'apiRequestClass' => AnetAPI\CreateTransactionRequest::class,
                'apiControllerClass' => AnetController\CreateTransactionController::class,
                'apiResponseClass' => AnetAPI\CreateTransactionResponse::class,
            ],
            'authorize type' => [
                'requestType' => Option\Transaction::AUTHORIZE,
                'apiRequestClass' => AnetAPI\CreateTransactionRequest::class,
                'apiControllerClass' => AnetController\CreateTransactionController::class,
                'apiResponseClass' => AnetAPI\CreateTransactionResponse::class,
            ],
            'capture type' => [
                'requestType' => Option\Transaction::CAPTURE,
                'apiRequestClass' => AnetAPI\CreateTransactionRequest::class,
                'apiControllerClass' => AnetController\CreateTransactionController::class,
                'apiResponseClass' => AnetAPI\CreateTransactionResponse::class,
            ]
        ];
    }

    /**
     * @expectedException \LogicException
     */
    public function testSendReturnsUnexpectedResponse()
    {
        $requestOptions = $this->getRequiredOptionsData();
        $requestType = Option\Transaction::CHARGE;

        $request = $this->createMock(AnetAPI\CreateTransactionRequest::class);
        $this->requestFactory->expects($this->once())
            ->method('createRequest')
            ->with($requestType, $requestOptions)
            ->willReturn($request);

        $errorResponse = new AnetAPI\ErrorResponse();
        $controller = $this->createMock(AnetController\CreateTransactionController::class);
        $controller->expects($this->once())->method('executeWithApiResponse')
            ->with(self::HOST_ADDRESS)
            ->willReturn($errorResponse);

        $this->requestFactory->expects($this->once())
            ->method('createController')
            ->with($request)
            ->willReturn($controller);

        $this->client->send(self::HOST_ADDRESS, $requestType, $requestOptions);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Unexpected Payment Gateway Error
     */
    public function testSendReturnsUnexpectedException()
    {
        $requestOptions = $this->getRequiredOptionsData();
        $requestType = Option\Transaction::CHARGE;

        $request = $this->createMock(AnetAPI\CreateTransactionRequest::class);
        $this->requestFactory->expects($this->once())
            ->method('createRequest')
            ->with($requestType, $requestOptions)
            ->willReturn($request);

        $controller = $this->createMock(AnetController\CreateTransactionController::class);
        $controller->expects($this->once())->method('executeWithApiResponse')
            ->with(self::HOST_ADDRESS)
            ->willThrowException(new \Exception('Unexpected Exception'));

        $this->requestFactory->expects($this->once())
            ->method('createController')
            ->with($request)
            ->willReturn($controller);

        $this->client->send(self::HOST_ADDRESS, $requestType, $requestOptions);
    }

    /**
     * @return array
     */
    protected function getRequiredOptionsData()
    {
        return [
            Option\ApiLoginId::API_LOGIN_ID => 'some_login_id',
            Option\TransactionKey::TRANSACTION_KEY => 'some_transaction_key',
        ];
    }
}
