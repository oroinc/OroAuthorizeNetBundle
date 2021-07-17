<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Behat\Mock\AuthorizeNet\Client\Factory\Api;

use net\authorize\api\contract\v1\CreateProfileResponseType;
use net\authorize\api\contract\v1\CreateTransactionRequest;
use net\authorize\api\contract\v1\CreateTransactionResponse;
use net\authorize\api\contract\v1\MessagesType;
use net\authorize\api\contract\v1\TransactionResponseType;
use net\authorize\api\contract\v1\TransactionResponseType\MessagesAType\MessageAType;
use Oro\Bundle\AuthorizeNetBundle\Entity\CustomerPaymentProfile;
use Oro\Bundle\AuthorizeNetBundle\EventListener\CreatePaymentProfileFromTransactionResponse;
use Oro\Bundle\AuthorizeNetBundle\Tests\Behat\Mock\Remote\Storage\PaymentProfileIDs;
use Oro\Bundle\AuthorizeNetBundle\Tests\Behat\Mock\Remote\Storage\PaymentProfileIDsAwareInterface;
use Oro\Bundle\AuthorizeNetBundle\Tests\Behat\Mock\Remote\Storage\PaymentProfileTypesToIDs;
use Oro\Bundle\AuthorizeNetBundle\Tests\Behat\Mock\Remote\Storage\PaymentProfileTypesToIDsAwareInterface;

class CreateTransactionControllerMock extends AbstractControllerMock implements
    PaymentProfileIDsAwareInterface,
    PaymentProfileTypesToIDsAwareInterface
{
    /** @var CreateTransactionRequest */
    private $request;

    /** @var PaymentProfileIDs */
    private $paymentProfileIdsStorage;

    /** @var PaymentProfileTypesToIDs $paymentProfileTypesToIDsStorage */
    private $paymentProfileTypesToIDsStorage;

    public function __construct(CreateTransactionRequest $request)
    {
        $this->request = $request;
    }

    public function setPaymentProfileIdsStorage(PaymentProfileIDs $paymentProfileIdsStorage)
    {
        $this->paymentProfileIdsStorage = $paymentProfileIdsStorage;
    }

    public function setPaymentProfileTypesToIDsStorage(PaymentProfileTypesToIDs $paymentProfileTypesToIDs)
    {
        $this->paymentProfileTypesToIDsStorage = $paymentProfileTypesToIDs;
    }

    /**
     * @param null|string $endPoint
     *
     * @return CreateTransactionResponse
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function executeWithApiResponse($endPoint = null): CreateTransactionResponse
    {
        $profile = $this->request->getTransactionRequest()->getProfile();
        if (null !== $profile) {
            $customerProfileId = $profile->getCustomerProfileId();
            $customer = $this->request->getTransactionRequest()->getCustomer();
            if (CreateCustomerProfileControllerMock::REGISTERED_CUSTOMER_PROFILE_ID !== $customerProfileId &&
                ($customer && $customer->getEmail() !== 'AmandaRCole@example.org' || !$customer)) {
                throw new \RuntimeException(
                    'Incorrect credentials got when try to pay and create profile with Authorize.Net!'
                );
            }

            if (true === $profile->getCreateProfile()) {
                return $this->getCreateProfileResponse();
            }

            return $this->getSuccessResponse();
        }

        $payment = $this->request->getTransactionRequest()->getPayment();
        if ($payment) {
            $value = $payment->getOpaqueData()->getDataValue();
            switch ($value) {
                case 'special_data_value_for_api_error_emulation':
                    return $this->getInvalidTokenErrorResponse();
                case 'special_data_value_for_not_approved_emulation':
                    return $this->getNotApprovedResponse();
            }
        }

        return $this->getSuccessResponse();
    }

    private function getCreateProfileResponse(): CreateTransactionResponse
    {
        $paymentProfileId = (string)uniqid();
        $this->paymentProfileIdsStorage->save($paymentProfileId);

        /**
         * Guess profile type, base on parameters from frontend
         * and save it to the storage
         */
        $dataValue = $this->request->getTransactionRequest()->getPayment()->getOpaqueData()->getDataValue();
        if ('echeck_data_value' === $dataValue) {
            $paymentProfileType = CustomerPaymentProfile::TYPE_ECHECK;
            $transactionResponse = $this->getEcheckSuccessResponse();
        } else {
            $paymentProfileType = CustomerPaymentProfile::TYPE_CREDITCARD;
            $transactionResponse = $this->getCreditCardSuccessResponse();
        }

        $this->paymentProfileTypesToIDsStorage->saveType(
            $paymentProfileId,
            $paymentProfileType
        );

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

    private function getInvalidTokenErrorResponse(): CreateTransactionResponse
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

    private function getSuccessResponse(): CreateTransactionResponse
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
            ->setCavvResultCode('2')
            ->setTransId('60022132422')
            ->setRefTransID('02886C4D3363CFE3E925548C84092F01')
            ->addToMessages(
                (new MessageAType())
                    ->setCode('1')
                    ->setDescription('This transaction has been approved.')
            );
        $response->setTransactionResponse($transactionResponse);

        return $response;
    }

    private function getNotApprovedResponse(): CreateTransactionResponse
    {
        $transId = '60022132422';
        $transactionRequest = $this->request->getTransactionRequest();
        if ($transactionRequest->getTransactionType() === 'authCaptureTransaction') {
            $transId = '60044567889';
        }

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
            ->setResponseCode('4')
            ->setAuthCode('01E43S')
            ->setAvsResultCode('Y')
            ->setCavvResultCode('2')
            ->setTransId($transId)
            ->setRefTransID('02886C4D3363CFE3E925548C84092F01')
            ->setTestRequest('0')
            ->setAccountNumber('123456789')
            ->addToMessages(
                (new MessageAType())
                    ->setCode('252')
                    ->setDescription('The transaction was accepted, but is being held for merchant review.')
            );
        $response->setTransactionResponse($transactionResponse);

        return $response;
    }

    private function getEcheckSuccessResponse(): CreateTransactionResponse
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
            ->setAccountType(CreatePaymentProfileFromTransactionResponse::ACCOUNT_TYPE_ECHECK)
            ->setAuthCode('01E43S')
            ->setAvsResultCode('Y')
            ->setCavvResultCode('2')
            ->setTransId('60022132422')
            ->setRefTransID('02886C4D3363CFE3E925548C84092F01')
            ->setTestRequest('0')
            ->setAccountNumber('123456789')
            ->addToMessages(
                (new MessageAType())
                    ->setCode('1')
                    ->setDescription('This transaction has been approved.')
            );
        $response->setTransactionResponse($transactionResponse);

        return $response;
    }

    private function getCreditCardSuccessResponse(): CreateTransactionResponse
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
            ->setAccountNumber('5424000000001500')
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
