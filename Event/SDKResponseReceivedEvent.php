<?php

namespace Oro\Bundle\AuthorizeNetBundle\Event;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Response\AuthorizeNetSDKResponse;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;
use Symfony\Contracts\EventDispatcher\Event;

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

    public function __construct(AuthorizeNetSDKResponse $response, PaymentTransaction $paymentTransaction)
    {
        $this->response = $response;
        $this->paymentTransaction = $paymentTransaction;
    }

    public function getResponse(): AuthorizeNetSDKResponse
    {
        return $this->response;
    }

    public function getPaymentTransaction(): PaymentTransaction
    {
        return $this->paymentTransaction;
    }
}
