<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Behat\Mock\AuthorizeNet\Client\Factory\Api;

use net\authorize\api\contract\v1\AnetApiRequestType;
use net\authorize\api\contract\v1\DeleteCustomerPaymentProfileRequest;
use net\authorize\api\contract\v1\DeleteCustomerPaymentProfileResponse;
use net\authorize\api\contract\v1\MessagesType;
use Oro\Bundle\AuthorizeNetBundle\Tests\Behat\Mock\Remote\Storage\PaymentProfileIDs;
use Oro\Bundle\AuthorizeNetBundle\Tests\Behat\Mock\Remote\Storage\PaymentProfileIDsAwareInterface;

class DeleteCustomerPaymentProfileControllerMock extends AbstractControllerMock implements
    PaymentProfileIDsAwareInterface
{
    /** @var AnetApiRequestType */
    protected $request;

    /** @var PaymentProfileIDs */
    protected $paymentProfileIdsStorage;

    /**
     * @param DeleteCustomerPaymentProfileRequest $request
     */
    public function __construct(DeleteCustomerPaymentProfileRequest $request)
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
     * @return DeleteCustomerPaymentProfileResponse
     */
    public function executeWithApiResponse($endPoint = null)
    {
        $removeResult = $this->paymentProfileIdsStorage->remove(
            $this->request->getCustomerPaymentProfileId()
        );

        $response = new DeleteCustomerPaymentProfileResponse();

        if ($removeResult) {
            $messages = new MessagesType();
            $messages->setResultCode('Ok');
        } else {
            $messages = new MessagesType();
            $messages->setResultCode('Error');
            $messages->addToMessage(
                (new MessagesType\MessageAType())
                    ->setCode('E00114')
                    ->setText('Incorrect payment profile id.')
            );
            $response->setMessages($messages);
        }

        $response->setMessages($messages);

        return $response;
    }
}
