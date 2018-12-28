<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Behat\Mock\AuthorizeNet\Client\Factory\Api;

use net\authorize\api\contract\v1\AnetApiRequestType;
use net\authorize\api\contract\v1\CreateCustomerPaymentProfileRequest;
use net\authorize\api\contract\v1\CreateCustomerPaymentProfileResponse;
use net\authorize\api\contract\v1\MessagesType;
use Oro\Bundle\AuthorizeNetBundle\Tests\Behat\Mock\Remote\Storage\PaymentProfileIDs;
use Oro\Bundle\AuthorizeNetBundle\Tests\Behat\Mock\Remote\Storage\PaymentProfileIDsAwareInterface;

class CreateCustomerPaymentProfileControllerMock extends AbstractControllerMock implements
    PaymentProfileIDsAwareInterface
{
    /** @var AnetApiRequestType */
    protected $request;

    /** @var PaymentProfileIDs */
    protected $paymentProfileIdsStorage;

    /**
     * @param CreateCustomerPaymentProfileRequest $request
     */
    public function __construct(CreateCustomerPaymentProfileRequest $request)
    {
        $this->request = $request;
    }

    /**
     * @param PaymentProfileIDs $paymentProfileIdsStorage
     */
    public function setPaymentProfileIdsStorage(PaymentProfileIDs $paymentProfileIdsStorage)
    {
        $this->paymentProfileIdsStorage = $paymentProfileIdsStorage;
    }

    /**
     * @param null|string $endPoint
     * @return CreateCustomerPaymentProfileResponse
     */
    public function executeWithApiResponse($endPoint = null)
    {
        $response = new CreateCustomerPaymentProfileResponse();
        $customerProfileId = $this->request->getCustomerProfileId();

        if (CreateCustomerProfileControllerMock::REGISTERED_CUSTOMER_PROFILE_ID === $customerProfileId) {
            $paymentProfileId = (string) uniqid();

            $this->paymentProfileIdsStorage->save($paymentProfileId);
            $response->setCustomerPaymentProfileId($paymentProfileId);

            $messages = new MessagesType();
            $messages->setResultCode('Ok');
        } else {
            $messages = new MessagesType();
            $messages->setResultCode('Error');
            $messages->addToMessage(
                (new MessagesType\MessageAType())
                    ->setCode('E00114')
                    ->setText('Invalid OTS Token.')
            );
        }

        $response->setMessages($messages);

        return $response;
    }
}
