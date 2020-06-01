<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Behat\Mock\AuthorizeNet\Client\Factory\Api;

use net\authorize\api\contract\v1\GetTransactionDetailsRequest;
use net\authorize\api\contract\v1\GetTransactionDetailsResponse;
use net\authorize\api\contract\v1\MessagesType;
use net\authorize\api\contract\v1\TransactionDetailsType;

/**
 * Mock service for handle get transaction details request and return the response
 */
class GetTransactionDetailsControllerMock extends AbstractControllerMock
{
    /**
     * @var GetTransactionDetailsRequest
     */
    private $request;

    /**
     * @param GetTransactionDetailsRequest $request
     */
    public function __construct(GetTransactionDetailsRequest $request)
    {
        $this->request = $request;
    }

    /**
     * @param null|string $endPoint
     *
     * @return GetTransactionDetailsResponse
     */
    public function executeWithApiResponse($endPoint = null): GetTransactionDetailsResponse
    {
        $transactionId = $this->request->getTransId();
        switch ($transactionId) {
            case '60022132422':
                $status = 'authorizedPendingCapture';
                break;
            case '60044567889':
                $status = 'capturedPendingSettlement';
                break;
            default:
                $status = 'FDSAuthorizedPendingReview';
        }

        return $this->getSuccessResponse($status);
    }

    /**
     * @param string $status
     * @return GetTransactionDetailsResponse
     */
    private function getSuccessResponse(string $status): GetTransactionDetailsResponse
    {
        $messages = new MessagesType();
        $messages->setResultCode('Ok');
        $messages->addToMessage(
            (new MessagesType\MessageAType())
                ->setCode('I00001')
                ->setText('Successful.')
        );

        $transaction = new TransactionDetailsType();
        $transaction
            ->setResponseCode('1')
            ->setAuthCode('01E43S')
            ->setTransId('60022132422')
            ->setRefTransID('02886C4D3363CFE3E925548C84092F01')
            ->setTransactionStatus($status)
            ;

        $response = new GetTransactionDetailsResponse();
        $response->setTransaction($transaction);
        $response->setMessages($messages);

        return $response;
    }
}
