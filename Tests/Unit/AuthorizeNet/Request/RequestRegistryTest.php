<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\PayPal\Payflow\Request;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Request\RequestInterface;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Request\RequestRegistry;

class RequestRegistryTest extends \PHPUnit\Framework\TestCase
{
    public function testGetInvalidRequest()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Request with type "X" is missing. Registered requests are "A, B"');

        $request1 = $this->createMock(RequestInterface::class);
        $request1->expects(self::once())
            ->method('getType')
            ->willReturn('A');
        $request2 = $this->createMock(RequestInterface::class);
        $request2->expects(self::once())
            ->method('getType')
            ->willReturn('B');

        $registry = new RequestRegistry([$request1, $request2]);
        $registry->getRequest('X');
    }

    public function testGetRequest()
    {
        $request1 = $this->createMock(RequestInterface::class);
        $request1->expects(self::once())
            ->method('getType')
            ->willReturn('A');
        $request2 = $this->createMock(RequestInterface::class);
        $request2->expects(self::once())
            ->method('getType')
            ->willReturn('B');
        $request3 = $this->createMock(RequestInterface::class);
        $request3->expects(self::once())
            ->method('getType')
            ->willReturn('C');

        $registry = new RequestRegistry([$request1, $request2, $request3]);

        self::assertSame($request2, $registry->getRequest('B'));
    }
}
