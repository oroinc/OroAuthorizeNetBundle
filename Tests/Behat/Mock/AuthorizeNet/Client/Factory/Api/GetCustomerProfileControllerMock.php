<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Behat\Mock\AuthorizeNet\Client\Factory\Api;

use net\authorize\api\contract\v1\AnetApiRequestType;
use net\authorize\api\contract\v1\CustomerPaymentProfileMaskedType;
use net\authorize\api\contract\v1\CustomerProfileMaskedType;
use net\authorize\api\contract\v1\GetCustomerProfileRequest;
use net\authorize\api\contract\v1\GetCustomerProfileResponse;
use net\authorize\api\contract\v1\MessagesType;
use Oro\Bundle\AuthorizeNetBundle\Tests\Behat\Mock\Remote\Storage\PaymentProfileIDs;
use Oro\Bundle\AuthorizeNetBundle\Tests\Behat\Mock\Remote\Storage\PaymentProfileIDsAwareInterface;

class GetCustomerProfileControllerMock extends AbstractControllerMock implements
    PaymentProfileIDsAwareInterface
{
    /** @var AnetApiRequestType */
    protected $request;

    /** @var PaymentProfileIDs */
    protected $paymentProfileIdsStorage;

    /**
     * @param GetCustomerProfileRequest $request
     */
    public function __construct(GetCustomerProfileRequest $request)
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
     * @return GetCustomerProfileResponse
     */
    public function executeWithApiResponse($endPoint = null)
    {
        $response = new GetCustomerProfileResponse();
        $customerProfileId = $this->request->getCustomerProfileId();

        if (CreateCustomerProfileControllerMock::REGISTERED_CUSTOMER_PROFILE_ID === $customerProfileId) {
            $customerProfile = new CustomerProfileMaskedType();
            $customerProfile->setCustomerProfileId(
                CreateCustomerProfileControllerMock::REGISTERED_CUSTOMER_PROFILE_ID
            );

            $paymentProfileIds = $this->paymentProfileIdsStorage->all();

            /**
             * Prepare all data with existed payment profiles
             */
            foreach ($paymentProfileIds as $paymentProfileId) {
                $customerPaymentProfile = new CustomerPaymentProfileMaskedType();
                $customerPaymentProfile->setCustomerProfileId(
                    CreateCustomerProfileControllerMock::REGISTERED_CUSTOMER_PROFILE_ID
                );
                $customerPaymentProfile->setCustomerPaymentProfileId($paymentProfileId);
                $customerProfile->addToPaymentProfiles($customerPaymentProfile);
            }

            $response->setProfile($customerProfile);

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
