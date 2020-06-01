<?php

namespace Oro\Bundle\AuthorizeNetBundle\Event;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Response\AuthorizeNetSDKResponse;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event on SDK Response received
 */
class SDKResponseReceivedEvent extends Event
{
    const NAME = 'oro_authorize_net.sdk_response.received';

    /** @var AuthorizeNetSDKResponse */
    private $response;

    /** @var PaymentTransaction */
    private $paymentTransaction;

    /**
     * @param AuthorizeNetSDKResponse $response
     * @param PaymentTransaction $paymentTransaction
     */
    public function __construct(AuthorizeNetSDKResponse $response, PaymentTransaction $paymentTransaction)
    {
        $this->response = $response;
        $this->paymentTransaction = $paymentTransaction;
    }

    /**
     * @return AuthorizeNetSDKResponse
     */
    public function getResponse(): AuthorizeNetSDKResponse
    {
        return $this->response;
    }

    /**
     * @return PaymentTransaction
     */
    public function getPaymentTransaction(): PaymentTransaction
    {
        return $this->paymentTransaction;
    }
}
