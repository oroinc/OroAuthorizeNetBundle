<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Behat\Mock\AuthorizeNet\Client\Factory\Api;

use net\authorize\api\contract\v1\CreateProfileResponseType;
use net\authorize\api\contract\v1\CreateTransactionRequest;
use net\authorize\api\contract\v1\CreateTransactionResponse;
use net\authorize\api\contract\v1\CustomerProfilePaymentType;
use net\authorize\api\contract\v1\MessagesType;
use net\authorize\api\contract\v1\TransactionResponseType;
use net\authorize\api\contract\v1\TransactionResponseType\MessagesAType\MessageAType;
use Oro\Bundle\AuthorizeNetBundle\Tests\Behat\Mock\Remote\Storage\PaymentProfileIDs;
use Oro\Bundle\AuthorizeNetBundle\Tests\Behat\Mock\Remote\Storage\PaymentProfileIDsAwareInterface;

class CreateTransactionControllerMock extends AbstractControllerMock implements PaymentProfileIDsAwareInterface
{
    /** @var CreateTransactionRequest */
    protected $request;

    /** @var PaymentProfileIDs */
    protected $paymentProfileIdsStorage;

    /**
     * @param CreateTransactionRequest $request
     */
    public function __construct(CreateTransactionRequest $request)
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
     * @return CreateTransactionResponse
     */
    public function executeWithApiResponse($endPoint = null)
    {
        $profile = $this->request->getTransactionRequest()->getProfile();
        if (null !== $profile) {
            return $this->getResponseByProfile($profile);
        }

        $payment = $this->request->getTransactionRequest()->getPayment();
        if ($payment && $payment->getOpaqueData()->getDataValue() === 'special_data_value_for_api_error_emulation') {
            return $this->getErrorResponse();
        }

        return $this->getSuccessResponse();
    }

    /**
     * @param CustomerProfilePaymentType $profile
     * @return CreateTransactionResponse
     */
    private function getResponseByProfile(CustomerProfilePaymentType $profile)
    {
        if (true === $profile->getCreateProfile()) {
            $paymentProfileId = (string) uniqid();
            $this->paymentProfileIdsStorage->save($paymentProfileId);

            $transactionResponse = $this->getSuccessResponse();

            $profileResponse = new CreateProfileResponseType();
            $profileResponse->setCustomerProfileId(
                CreateCustomerProfileControllerMock::REGISTERED_CUSTOMER_PROFILE_ID
            );
            $profileResponse->setCustomerPaymentProfileIdList([$paymentProfileId]);

            $messages = new MessagesType();
            $messages->setResultCode('Ok');
            $profileResponse->setMessages($messages);

            $transactionResponse->setProfileResponse($profileResponse);

            return $transactionResponse;
        }

        if (CreateCustomerProfileControllerMock::REGISTERED_CUSTOMER_PROFILE_ID === $profile->getCustomerProfileId()) {
            return $this->getSuccessResponse();
        }

        return $this->getErrorResponse();
    }

    /**
     * @return CreateTransactionResponse
     */
    private function getErrorResponse()
    {
        $response = new CreateTransactionResponse();

        $messages = new MessagesType();
        $messages->setResultCode('Error');
        $messages->addToMessage(
            (new MessagesType\MessageAType())
                ->setCode('E00114')
                ->setText('Invalid OTS Token.')
        );
        $response->setMessages($messages);

        $response->setTransactionResponse(new TransactionResponseType());

        return $response;
    }

    /**
     * @return CreateTransactionResponse
     */
    private function getSuccessResponse()
    {
        $response = new CreateTransactionResponse();

        $messages = new MessagesType();
        $messages->setResultCode('Ok');
        $messages->addToMessage(
            (new MessagesType\MessageAType())
                ->setCode('I00001')
                ->setText('Successful.')
        );
        $response->setMessages($messages);

        $transactionResponse = new TransactionResponseType();
        $transactionResponse
            ->setResponseCode('1')
            ->setAuthCode('01E43S')
            ->setAvsResultCode('Y')
            ->setCvvResultCode('P')
            ->setCavvResultCode('2')
            ->setTransId('60022132422')
            ->setRefTransID('02886C4D3363CFE3E925548C84092F01')
            ->setTestRequest('0')
            ->setAccountNumber('XXXX1500')
            ->setAccountType('MasterCard')
            ->addToMessages(
                (new MessageAType())
                    ->setCode('1')
                    ->setDescription('This transaction has been approved.')
            );
        $response->setTransactionResponse($transactionResponse);

        return $response;
    }
}
