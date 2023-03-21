<?php

namespace Oro\Bundle\SaleBundle\Tests\Unit\Event;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Response\AuthorizeNetSDKResponse;
use Oro\Bundle\AuthorizeNetBundle\Event\SDKResponseReceivedEvent;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;

class SDKResponseReceivedEventTest extends \PHPUnit\Framework\TestCase
{
    public function testGetters(): void
    {
        $transaction = new PaymentTransaction();
        $response = $this->createMock(AuthorizeNetSDKResponse::class);

        $event = new SDKResponseReceivedEvent($response, $transaction);

        $this->assertSame($response, $event->getResponse());
        $this->assertSame($transaction, $event->getPaymentTransaction());
    }
}
