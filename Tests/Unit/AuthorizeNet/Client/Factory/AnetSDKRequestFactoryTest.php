<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Client\Factory;

use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Client\Factory\AnetSDKRequestFactory;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Client\RequestConfigurator\RequestConfiguratorInterface;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Client\RequestConfigurator\RequestConfiguratorRegistry;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option\Transaction;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Request as Request;

class AnetSDKRequestFactoryTest extends \PHPUnit\Framework\TestCase
{
    /** @var AnetSDKRequestFactory */
    protected $factory;

    /** @var RequestConfiguratorRegistry|\PHPUnit\Framework\MockObject\MockObject */
    protected $requestConfiguratorRegistry;

    protected function setUp()
    {
        $this->requestConfiguratorRegistry = $this->createMock(RequestConfiguratorRegistry::class);
        $this->factory = new AnetSDKRequestFactory($this->requestConfiguratorRegistry);
    }

    protected function tearDown()
    {
        unset($this->factory, $this->requestConfiguratorRegistry);
    }

    /**
     * @dataProvider createRequestDataProvider
     * @param string $requestType
     * @param string $apiRequestClass
     */
    public function testCreateRequest($requestType, $apiRequestClass)
    {
        $options = [];
        $requestType = $requestType;
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

        $this->requestConfiguratorRegistry->expects($this->once())
            ->method('getRequestConfigurators')
            ->willReturn([$requestConfigurator1, $requestConfigurator2]);

        $request = $this->factory->createRequest($requestType, $options);

        $this->assertInstanceOf($apiRequestClass, $request);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Unsupported request type
     */
    public function testCreateRequestUnsupportedRequestType()
    {
        $this->factory->createRequest('unsupported_request_type');
    }

    /**
     * @dataProvider createControllerDataProvider
     * @param string $apiRequestClass
     * @param string $apiControllerClass
     */
    public function testCreateController($apiRequestClass, $apiControllerClass)
    {
        /** @var  $request AnetAPI\ANetApiRequestType */
        $request = new $apiRequestClass;
        $request->setMerchantAuthentication(new AnetAPI\MerchantAuthenticationType());

        $controller = $this->factory->createController($request);

        $this->assertInstanceOf($apiControllerClass, $controller);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Unsupported request class
     */
    public function testCreateControllerUnsupportedRequest()
    {
        $this->factory->createController(new AnetAPI\ANetApiRequestType());
    }

    /**
     * @return array
     */
    public function createControllerDataProvider()
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

    /**
     * @return array
     */
    public function createRequestDataProvider()
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
