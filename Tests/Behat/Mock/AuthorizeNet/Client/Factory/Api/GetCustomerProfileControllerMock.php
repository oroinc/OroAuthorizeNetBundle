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
    private $request;

    /** @var PaymentProfileIDs */
    private $paymentProfileIdsStorage;

    public function __construct(GetCustomerProfileRequest $request)
    {
        $this->request = $request;
    }

    #[\Override]
    public function setPaymentProfileIdsStorage(PaymentProfileIDs $paymentProfileIdsStorage)
    {
        $this->paymentProfileIdsStorage = $paymentProfileIdsStorage;
    }

    /**
     * @param null|string $endPoint
     * @return GetCustomerProfileResponse
     */
    #[\Override]
    public function executeWithApiResponse($endPoint = null): GetCustomerProfileResponse
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
                    ->setCode('E00098')
                    ->setText('Customer Profile ID or Shipping Profile ID not found.')
            );
        }

        $response->setMessages($messages);

        return $response;
    }
}
