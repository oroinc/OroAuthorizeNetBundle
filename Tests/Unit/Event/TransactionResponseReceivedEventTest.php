<?php

namespace Oro\Bundle\SaleBundle\Tests\Unit\Event;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Response\AuthorizeNetSDKTransactionResponse;
use Oro\Bundle\AuthorizeNetBundle\Event\TransactionResponseReceivedEvent;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;

class TransactionResponseReceivedEventTest extends \PHPUnit\Framework\TestCase
{
    public function testGetters()
    {
        $transaction = new PaymentTransaction();
        $response = $this->createMock(AuthorizeNetSDKTransactionResponse::class);

        $event = new TransactionResponseReceivedEvent($response, $transaction);

        $this->assertSame($response, $event->getResponse());
        $this->assertSame($transaction, $event->getPaymentTransaction());
    }
}
