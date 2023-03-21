<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Client\Factory;

use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Client\Factory\AnetSDKRequestFactory;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Client\RequestConfigurator\RequestConfiguratorInterface;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option\Transaction;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Request;

class AnetSDKRequestFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider createRequestDataProvider
     */
    public function testCreateRequest(string $requestType, string $apiRequestClass)
    {
        $options = [];
        $transactionRequest = new $apiRequestClass;

        $requestConfigurator1 = $this->createMock(RequestConfiguratorInterface::class);
        $requestConfigurator1->expects($this->once())
            ->method('isApplicable')
            ->with($transactionRequest, $options)
            ->willReturn(true);
        $requestConfigurator1->expects($this->once())
            ->method('handle')
            ->with($transactionRequest, $options);

        $requestConfigurator2 = $this->createMock(RequestConfiguratorInterface::class);
        $requestConfigurator2->expects($this->once())
            ->method('isApplicable')
            ->with($transactionRequest, $options)
            ->willReturn(false);
        $requestConfigurator2->expects($this->never())
            ->method('handle');

        $factory = new AnetSDKRequestFactory([$requestConfigurator1, $requestConfigurator2]);
        $request = $factory->createRequest($requestType, $options);

        $this->assertInstanceOf($apiRequestClass, $request);
    }

    public function testCreateRequestUnsupportedRequestType()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported request type');

        $factory = new AnetSDKRequestFactory([]);
        $factory->createRequest('unsupported_request_type');
    }

    /**
     * @dataProvider createControllerDataProvider
     */
    public function testCreateController(string $apiRequestClass, string $apiControllerClass)
    {
        /** @var AnetAPI\ANetApiRequestType $request */
        $request = new $apiRequestClass;
        $request->setMerchantAuthentication(new AnetAPI\MerchantAuthenticationType());

        $factory = new AnetSDKRequestFactory([]);
        $controller = $factory->createController($request);

        $this->assertInstanceOf($apiControllerClass, $controller);
    }

    public function testCreateControllerUnsupportedRequest()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported request class');

        $factory = new AnetSDKRequestFactory([]);
        $factory->createController(new AnetAPI\ANetApiRequestType());
    }

    public function createControllerDataProvider(): array
    {
        return [
            'create_transaction_request' => [
                'apiRequestClass' => AnetAPI\CreateTransactionRequest::class,
                'apiControllerClass' => AnetController\CreateTransactionController::class
            ],
            'create_customer_profile_request' => [
                'apiRequestClass' => AnetAPI\CreateCustomerProfileRequest::class,
                'apiControllerClass' => AnetController\CreateCustomerProfileController::class
            ],
            'delete_customer_profile_request' => [
                'apiRequestClass' => AnetAPI\DeleteCustomerProfileRequest::class,
                'apiControllerClass' => AnetController\DeleteCustomerProfileController::class
            ],
            'create_customer_payment_profile_request' => [
                'apiRequestClass' => AnetAPI\CreateCustomerPaymentProfileRequest::class,
                'apiControllerClass' => AnetController\CreateCustomerPaymentProfileController::class
            ],
            'update_customer_payment_profile_request' => [
                'apiRequestClass' => AnetAPI\UpdateCustomerPaymentProfileRequest::class,
                'apiControllerClass' => AnetController\UpdateCustomerPaymentProfileController::class
            ],
            'get_customer_payment_profile_request' => [
                'apiRequestClass' => AnetAPI\GetCustomerPaymentProfileRequest::class,
                'apiControllerClass' => AnetController\GetCustomerPaymentProfileController::class
            ],
            'delete_customer_payment_profile_request' => [
                'apiRequestClass' => AnetAPI\DeleteCustomerPaymentProfileRequest::class,
                'apiControllerClass' => AnetController\DeleteCustomerPaymentProfileController::class
            ],
            'get_customer_profile_request' => [
                'apiRequestClass' => AnetAPI\GetCustomerProfileRequest::class,
                'apiControllerClass' => AnetController\GetCustomerProfileController::class
            ],
            'get_transaction_details_request' => [
                'apiRequestClass' => AnetAPI\GetTransactionDetailsRequest::class,
                'apiControllerClass' => AnetController\GetTransactionDetailsController::class
            ]
        ];
    }

    public function createRequestDataProvider(): array
    {
        return [
            'charge type' => [
                'requestType' => Transaction::CHARGE,
                'apiRequestClass' => AnetAPI\CreateTransactionRequest::class
            ],
            'authorize type' => [
                'requestType' => Transaction::AUTHORIZE,
                'apiRequestClass' => AnetAPI\CreateTransactionRequest::class
            ],
            'capture type' => [
                'requestType' => Transaction::CAPTURE,
                'apiRequestClass' => AnetAPI\CreateTransactionRequest::class
            ],
            'create customer profile' => [
                'requestType' => Request\CreateCustomerProfileRequest::REQUEST_TYPE,
                'apiRequestClass' => AnetAPI\CreateCustomerProfileRequest::class
            ],
            'delete customer profile' => [
                'requestType' => Request\DeleteCustomerProfileRequest::REQUEST_TYPE,
                'apiRequestClass' => AnetAPI\DeleteCustomerProfileRequest::class
            ],
            'create customer payment profile' => [
                'requestType' => Request\CreateCustomerPaymentProfileRequest::REQUEST_TYPE,
                'apiRequestClass' => AnetAPI\CreateCustomerPaymentProfileRequest::class
            ],
            'update customer payment profile' => [
                'requestType' => Request\UpdateCustomerPaymentProfileRequest::REQUEST_TYPE,
                'apiRequestClass' => AnetAPI\UpdateCustomerPaymentProfileRequest::class
            ],
            'get customer payment profile' => [
                'requestType' => Request\GetCustomerPaymentProfileRequest::REQUEST_TYPE,
                'apiRequestClass' => AnetAPI\GetCustomerPaymentProfileRequest::class
            ],
            'delete customer payment profile' => [
                'requestType' => Request\DeleteCustomerPaymentProfileRequest::REQUEST_TYPE,
                'apiRequestClass' => AnetAPI\DeleteCustomerPaymentProfileRequest::class
            ],
            'get customer profile' => [
                'requestType' => Request\GetCustomerProfileRequest::REQUEST_TYPE,
                'apiRequestClass' => AnetAPI\GetCustomerProfileRequest::class
            ],
            'get transaction details' => [
                'requestType' => Request\GetTransactionDetailsRequest::REQUEST_TYPE,
                'apiRequestClass' => AnetAPI\GetTransactionDetailsRequest::class
            ]
        ];
    }
}
