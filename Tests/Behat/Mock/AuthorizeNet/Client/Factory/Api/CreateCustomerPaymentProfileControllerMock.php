<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Behat\Mock\AuthorizeNet\Client\Factory\Api;

use net\authorize\api\contract\v1\AnetApiRequestType;
use net\authorize\api\contract\v1\CreateCustomerPaymentProfileRequest;
use net\authorize\api\contract\v1\CreateCustomerPaymentProfileResponse;
use net\authorize\api\contract\v1\MessagesType;
use Oro\Bundle\AuthorizeNetBundle\Entity\CustomerPaymentProfile;
use Oro\Bundle\AuthorizeNetBundle\Tests\Behat\Mock\Remote\Storage\PaymentProfileIDs;
use Oro\Bundle\AuthorizeNetBundle\Tests\Behat\Mock\Remote\Storage\PaymentProfileIDsAwareInterface;
use Oro\Bundle\AuthorizeNetBundle\Tests\Behat\Mock\Remote\Storage\PaymentProfileTypesToIDs;
use Oro\Bundle\AuthorizeNetBundle\Tests\Behat\Mock\Remote\Storage\PaymentProfileTypesToIDsAwareInterface;

class CreateCustomerPaymentProfileControllerMock extends AbstractControllerMock implements
    PaymentProfileIDsAwareInterface,
    PaymentProfileTypesToIDsAwareInterface
{
    /** @var AnetApiRequestType */
    private $request;

    /** @var PaymentProfileIDs */
    private $paymentProfileIdsStorage;

    /** @var PaymentProfileTypesToIDs */
    private $paymentProfileTypesToIDsStorage;

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
     * @param PaymentProfileTypesToIDs $paymentProfileTypesToIDs
     */
    public function setPaymentProfileTypesToIDsStorage(PaymentProfileTypesToIDs $paymentProfileTypesToIDs)
    {
        $this->paymentProfileTypesToIDsStorage = $paymentProfileTypesToIDs;
    }

    /**
     * @param null|string $endPoint
     * @return CreateCustomerPaymentProfileResponse
     */
    public function executeWithApiResponse($endPoint = null): CreateCustomerPaymentProfileResponse
    {
        $response = new CreateCustomerPaymentProfileResponse();
        $customerProfileId = $this->request->getCustomerProfileId();

        if (CreateCustomerProfileControllerMock::REGISTERED_CUSTOMER_PROFILE_ID === $customerProfileId) {
            $paymentProfileId = (string) uniqid();

            $this->paymentProfileIdsStorage->save($paymentProfileId);
            $response->setCustomerPaymentProfileId($paymentProfileId);

            /**
             * Guess profile type, base on parameters from frontend and save it to the storage
             */
            $paymentDataValue = $this->request->getPaymentProfile()->getPayment()->getOpaqueData()->getDataValue();
            $paymentProfileType = 'echeck_data_value' === $paymentDataValue ?
                CustomerPaymentProfile::TYPE_ECHECK :
                CustomerPaymentProfile::TYPE_CREDITCARD;

            $this->paymentProfileTypesToIDsStorage->saveType(
                $paymentProfileId,
                $paymentProfileType
            );

            $messages = new MessagesType();
            $messages->setResultCode('Ok');
        } else {
            $messages = new MessagesType();
            $messages->setResultCode('Error');
            $messages->addToMessage(
                (new MessagesType\MessageAType())
                    ->setCode('E00098')
                    ->setText('Customer Profile ID or Shipping Profile ID not found.')
            );
        }

        $response->setMessages($messages);

        return $response;
    }
}
