<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Behat\Mock\AuthorizeNet\Client\Factory;

use net\authorize\api\contract\v1 as AnetAPI;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Client\Factory\AnetSDKRequestFactory;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option\Transaction;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Request as Request;
use Oro\Bundle\AuthorizeNetBundle\Tests\Behat\Mock\AuthorizeNet\Client\Factory\Api as MockControllers;
use Oro\Bundle\AuthorizeNetBundle\Tests\Behat\Mock\Remote\Storage\PaymentProfileIDs;
use Oro\Bundle\AuthorizeNetBundle\Tests\Behat\Mock\Remote\Storage\PaymentProfileIDsAwareInterface;

final class AnetSDKRequestFactoryMock extends AnetSDKRequestFactory
{
    /** @var PaymentProfileIDs */
    protected $remoteStoragePaymentProfileIds;

    /** @var array */
    protected static $requestClassMap = [
        Transaction::AUTHORIZE => AnetAPI\CreateTransactionRequest::class,
        Transaction::CAPTURE => AnetAPI\CreateTransactionRequest::class,
        Transaction::CHARGE => AnetAPI\CreateTransactionRequest::class,
        Request\CreateCustomerProfileRequest::REQUEST_TYPE => AnetAPI\CreateCustomerProfileRequest::class,
        Request\CreateCustomerPaymentProfileRequest::REQUEST_TYPE => AnetAPI\CreateCustomerPaymentProfileRequest::class,
        Request\GetCustomerProfileRequest::REQUEST_TYPE => AnetAPI\GetCustomerProfileRequest::class,
        Request\DeleteCustomerPaymentProfileRequest::REQUEST_TYPE => AnetAPI\DeleteCustomerPaymentProfileRequest::class,
        Request\UpdateCustomerPaymentProfileRequest::REQUEST_TYPE => AnetAPI\UpdateCustomerPaymentProfileRequest::class,
        Request\GetCustomerPaymentProfileRequest::REQUEST_TYPE => AnetAPI\GetCustomerPaymentProfileRequest::class,
    ];

    /** @var array */
    protected static $controllerClassMap = [
        AnetAPI\CreateTransactionRequest::class => MockControllers\CreateTransactionControllerMock::class,
        AnetAPI\CreateCustomerProfileRequest::class => MockControllers\CreateCustomerProfileControllerMock::class,
        AnetAPI\CreateCustomerPaymentProfileRequest::class =>
            MockControllers\CreateCustomerPaymentProfileControllerMock::class,
        AnetAPI\GetCustomerProfileRequest::class =>
            MockControllers\GetCustomerProfileControllerMock::class,
        AnetAPI\DeleteCustomerPaymentProfileRequest::class =>
            MockControllers\DeleteCustomerPaymentProfileControllerMock::class,
        AnetAPI\UpdateCustomerPaymentProfileRequest::class =>
            MockControllers\UpdateCustomerPaymentProfileControllerMock::class,
        AnetAPI\GetCustomerPaymentProfileRequest::class =>
            MockControllers\GetCustomerPaymentProfileControllerMock::class
    ];

    /**
     * @param PaymentProfileIDs $remoteStoragePaymentProfileIds
     */
    public function setRemoteStoragePaymentProfileIds(PaymentProfileIDs $remoteStoragePaymentProfileIds)
    {
        $this->remoteStoragePaymentProfileIds = $remoteStoragePaymentProfileIds;
    }

    /**
     * {@inheritdoc}
     */
    public function createController(AnetAPI\ANetApiRequestType $request)
    {
        $controller = parent::createController($request);

        if ($controller instanceof PaymentProfileIDsAwareInterface) {
            $controller->setPaymentProfileIdsStorage($this->remoteStoragePaymentProfileIds);
        }

        return $controller;
    }
}
