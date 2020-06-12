<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Client\Factory;

use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Client\RequestConfigurator\RequestConfiguratorRegistry;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option\Transaction;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Request as Request;

/**
 * Factory for creating appropriate API request object by request type
 * & creating API controller object by API request object
 */
class AnetSDKRequestFactory implements AnetSDKRequestFactoryInterface
{
    /**
     * @var RequestConfiguratorRegistry
     */
    private $requestConfiguratorRegistry;

    /** @var array */
    protected static $requestClassMap = [
        Transaction::AUTHORIZE => AnetAPI\CreateTransactionRequest::class,
        Transaction::CAPTURE => AnetAPI\CreateTransactionRequest::class,
        Transaction::CHARGE => AnetAPI\CreateTransactionRequest::class,
        Request\CreateCustomerProfileRequest::REQUEST_TYPE => AnetAPI\CreateCustomerProfileRequest::class,
        Request\DeleteCustomerProfileRequest::REQUEST_TYPE => AnetAPI\DeleteCustomerProfileRequest::class,
        Request\CreateCustomerPaymentProfileRequest::REQUEST_TYPE => AnetAPI\CreateCustomerPaymentProfileRequest::class,
        Request\UpdateCustomerPaymentProfileRequest::REQUEST_TYPE => AnetAPI\UpdateCustomerPaymentProfileRequest::class,
        Request\GetCustomerPaymentProfileRequest::REQUEST_TYPE => AnetAPI\GetCustomerPaymentProfileRequest::class,
        Request\GetCustomerProfileRequest::REQUEST_TYPE => AnetAPI\GetCustomerProfileRequest::class,
        Request\DeleteCustomerPaymentProfileRequest::REQUEST_TYPE => AnetAPI\DeleteCustomerPaymentProfileRequest::class,
        Request\AuthenticateTestRequest::REQUEST_TYPE => AnetAPI\AuthenticateTestRequest::class,
        Request\GetTransactionDetailsRequest::REQUEST_TYPE => AnetAPI\GetTransactionDetailsRequest::class,
    ];

    /** @var array */
    protected static $controllerClassMap = [
        AnetAPI\CreateTransactionRequest::class => AnetController\CreateTransactionController::class,
        AnetAPI\CreateCustomerProfileRequest::class => AnetController\CreateCustomerProfileController::class,
        AnetAPI\DeleteCustomerProfileRequest::class => AnetController\DeleteCustomerProfileController::class,
        AnetAPI\CreateCustomerPaymentProfileRequest::class =>
            AnetController\CreateCustomerPaymentProfileController::class,
        AnetAPI\UpdateCustomerPaymentProfileRequest::class =>
            AnetController\UpdateCustomerPaymentProfileController::class,
        AnetAPI\GetCustomerPaymentProfileRequest::class => AnetController\GetCustomerPaymentProfileController::class,
        AnetAPI\GetCustomerProfileRequest::class => AnetController\GetCustomerProfileController::class,
        AnetAPI\DeleteCustomerPaymentProfileRequest::class =>
            AnetController\DeleteCustomerPaymentProfileController::class,
        AnetAPI\AuthenticateTestRequest::class => AnetController\AuthenticateTestController::class,
        AnetAPI\GetTransactionDetailsRequest::class => AnetController\GetTransactionDetailsController::class,
    ];

    /**
     * {@inheritdoc}
     */
    public function __construct(RequestConfiguratorRegistry $requestConfiguratorRegistry)
    {
        $this->requestConfiguratorRegistry = $requestConfiguratorRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function createRequest(string $type, array $options = [])
    {
        if (!array_key_exists($type, static::$requestClassMap)) {
            throw new \InvalidArgumentException('Unsupported request type');
        }

        $request = new static::$requestClassMap[$type];

        $configurators = $this->requestConfiguratorRegistry->getRequestConfigurators();

        foreach ($configurators as $configurator) {
            if ($configurator->isApplicable($request, $options)) {
                $configurator->handle($request, $options);
            }
        }

        return $request;
    }

    /**
     * {@inheritdoc}
     */
    public function createController(AnetAPI\ANetApiRequestType $request)
    {
        $requestClass = \get_class($request);

        if (!array_key_exists($requestClass, static::$controllerClassMap)) {
            throw new \InvalidArgumentException('Unsupported request class');
        }

        return new static::$controllerClassMap[$requestClass]($request);
    }
}
