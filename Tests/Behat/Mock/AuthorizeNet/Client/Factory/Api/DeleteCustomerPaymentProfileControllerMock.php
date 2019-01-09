<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Behat\Mock\AuthorizeNet\Client\Factory\Api;

use net\authorize\api\contract\v1\AnetApiRequestType;
use net\authorize\api\contract\v1\DeleteCustomerPaymentProfileRequest;
use net\authorize\api\contract\v1\DeleteCustomerPaymentProfileResponse;
use net\authorize\api\contract\v1\MessagesType;
use Oro\Bundle\AuthorizeNetBundle\Tests\Behat\Mock\Remote\Storage\PaymentProfileIDs;
use Oro\Bundle\AuthorizeNetBundle\Tests\Behat\Mock\Remote\Storage\PaymentProfileIDsAwareInterface;
use Oro\Bundle\AuthorizeNetBundle\Tests\Behat\Mock\Remote\Storage\PaymentProfileTypesToIDs;
use Oro\Bundle\AuthorizeNetBundle\Tests\Behat\Mock\Remote\Storage\PaymentProfileTypesToIDsAwareInterface;

class DeleteCustomerPaymentProfileControllerMock extends AbstractControllerMock implements
    PaymentProfileIDsAwareInterface,
    PaymentProfileTypesToIDsAwareInterface
{
    /** @var AnetApiRequestType */
    private $request;

    /** @var PaymentProfileIDs */
    private $paymentProfileIdsStorage;

    /** @var PaymentProfileTypesToIDs $paymentProfileTypesToIDsStorage */
    private $paymentProfileTypesToIDsStorage;

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
     * @param PaymentProfileTypesToIDs $paymentProfileTypesToIDs
     */
    public function setPaymentProfileTypesToIDsStorage(PaymentProfileTypesToIDs $paymentProfileTypesToIDs)
    {
        $this->paymentProfileTypesToIDsStorage = $paymentProfileTypesToIDs;
    }

    /**
     * @param null|string $endPoint
     * @return DeleteCustomerPaymentProfileResponse
     */
    public function executeWithApiResponse($endPoint = null): DeleteCustomerPaymentProfileResponse
    {
        $removeResult = $this->paymentProfileIdsStorage->remove(
            $this->request->getCustomerPaymentProfileId()
        );

        $this->paymentProfileTypesToIDsStorage->removeId(
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
                    ->setCode('I00004')
                    ->setText('No records found.')
            );
        }

        $response->setMessages($messages);

        return $response;
    }
}
