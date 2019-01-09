<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Behat\Mock\AuthorizeNet\Client\Factory\Api;

use net\authorize\api\contract\v1\AnetApiRequestType;
use net\authorize\api\contract\v1\MessagesType;
use net\authorize\api\contract\v1\UpdateCustomerPaymentProfileRequest;
use net\authorize\api\contract\v1\UpdateCustomerPaymentProfileResponse;
use Oro\Bundle\AuthorizeNetBundle\Tests\Behat\Mock\Remote\Storage\PaymentProfileIDs;
use Oro\Bundle\AuthorizeNetBundle\Tests\Behat\Mock\Remote\Storage\PaymentProfileIDsAwareInterface;

class UpdateCustomerPaymentProfileControllerMock extends AbstractControllerMock implements
    PaymentProfileIDsAwareInterface
{
    /** @var AnetApiRequestType */
    private $request;

    /** @var PaymentProfileIDs */
    private $paymentProfileIdsStorage;

    /**
     * @param UpdateCustomerPaymentProfileRequest $request
     */
    public function __construct(UpdateCustomerPaymentProfileRequest $request)
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
     * @return UpdateCustomerPaymentProfileResponse
     */
    public function executeWithApiResponse($endPoint = null): UpdateCustomerPaymentProfileResponse
    {
        $recordExists = $this->paymentProfileIdsStorage->exists(
            $this->request->getPaymentProfile()->getCustomerPaymentProfileId()
        );

        $response = new UpdateCustomerPaymentProfileResponse();

        if ($recordExists) {
            $messages = new MessagesType();
            $messages->setResultCode('Ok');
        } else {
            $messages = new MessagesType();
            $messages->setResultCode('Error');
            $messages->addToMessage(
                (new MessagesType\MessageAType())
                    ->setCode('I00004')
                    ->setText('No records found.')
            );
        }

        $response->setMessages($messages);

        return $response;
    }
}
