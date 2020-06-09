<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Response;

use net\authorize\api\contract\v1\CreateTransactionResponse;
use net\authorize\api\contract\v1\TransactionResponseType\ErrorsAType\ErrorAType;
use net\authorize\api\contract\v1\TransactionResponseType\MessagesAType\MessageAType;

/**
 * TransactionResponse class to represent AuthorizeNet API CreateTransactionResponse
 */
class AuthorizeNetSDKTransactionResponse extends AuthorizeNetSDKResponse
{
    protected static $allowResponses = [
        self::TRANS_SUCCESSFUL_RESPONSE_CODE,
        self::TRANS_NOT_APPROVED_RESPONSE_CODE
    ];

    /**
     * @var CreateTransactionResponse
     */
    protected $apiResponse;

    /**
     * {@inheritdoc}
     */
    public function isSuccessful()
    {
        $transactionResponse = $this->apiResponse->getTransactionResponse();

        if ($transactionResponse) {
            return in_array($transactionResponse->getResponseCode(), self::$allowResponses, true);
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isActive()
    {
        $transactionResponse = $this->apiResponse->getTransactionResponse();

        return $transactionResponse &&
            $transactionResponse->getResponseCode() === self::TRANS_SUCCESSFUL_RESPONSE_CODE;
    }

    /**
     * {@inheritdoc}
     */
    public function getReference()
    {
        $transactionResponse = $this->apiResponse->getTransactionResponse();

        return $transactionResponse ? $transactionResponse->getTransId() : null;
    }

    /**
     * @return null|string
     */
    protected function getSuccessMessage()
    {
        $messages = $this->collectMessages();

        $transactionResponse = $this->apiResponse->getTransactionResponse();
        if ($transactionResponse) {
            /** @var MessageAType[]|null $transactionMessages */
            $transactionMessages = $transactionResponse->getMessages();
            if ($transactionMessages) { // $transactionResponse->getMessages() can return null sometimes
                foreach ($transactionMessages as $message) {
                    $messages[] = "({$message->getCode()}) {$message->getDescription()}";
                }
            }
        }

        return empty($messages) ? null : implode(';  ', $messages);
    }

    /**
     * @return null|string
     */
    protected function getErrorMessage()
    {
        $errorMessages = $this->collectMessages();

        $transactionResponse = $this->apiResponse->getTransactionResponse();
        if ($transactionResponse) {
            /** @var ErrorAType[]|null $transactionErrors */
            $transactionErrors = $transactionResponse->getErrors();
            if ($transactionErrors) { // $transactionResponse->getErrors() can return null sometimes
                foreach ($transactionErrors as $error) {
                    $errorMessages[] = "({$error->getErrorCode()}) {$error->getErrorText()}";
                }
            }
        }

        return empty($errorMessages) ? null : implode(';  ', $errorMessages);
    }
}
