<?php

namespace Oro\Bundle\AuthorizeNetBundle\Event;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Response\AuthorizeNetSDKTransactionResponse;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event on Transaction Response received
 */
class TransactionResponseReceivedEvent extends Event
{
    const NAME = 'oro_authorize_net.transaction_response.received';

    /** @var AuthorizeNetSDKTransactionResponse */
    private $response;

    /** @var PaymentTransaction */
    private $paymentTransaction;

    /**
     * @param AuthorizeNetSDKTransactionResponse $response
     * @param PaymentTransaction $paymentTransaction
     */
    public function __construct(AuthorizeNetSDKTransactionResponse $response, PaymentTransaction $paymentTransaction)
    {
        $this->response = $response;
        $this->paymentTransaction = $paymentTransaction;
    }

    /**
     * @return AuthorizeNetSDKTransactionResponse
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @return PaymentTransaction
     */
    public function getPaymentTransaction()
    {
        return $this->paymentTransaction;
    }
}
