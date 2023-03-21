<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\Method\Factory;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Gateway;
use Oro\Bundle\AuthorizeNetBundle\Method\AuthorizeNetPaymentMethod;
use Oro\Bundle\AuthorizeNetBundle\Method\Config\AuthorizeNetConfigInterface;
use Oro\Bundle\AuthorizeNetBundle\Method\Factory\AuthorizeNetPaymentMethodFactory;
use Oro\Bundle\AuthorizeNetBundle\Method\Option\Resolver\MethodOptionResolverInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class AuthorizeNetPaymentMethodFactoryTest extends \PHPUnit\Framework\TestCase
{
    /** @var Gateway|\PHPUnit\Framework\MockObject\MockObject */
    private $gateway;

    /** @var LoggerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $logger;

    /** @var RequestStack|\PHPUnit\Framework\MockObject\MockObject */
    private $requestStack;

    /** @var MethodOptionResolverInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $methodOptionResolver;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /** @var AuthorizeNetPaymentMethodFactory */
    private $factory;

    protected function setUp(): void
    {
        $this->gateway = $this->createMock(Gateway::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->requestStack = $this->createMock(RequestStack::class);
        $this->methodOptionResolver = $this->createMock(MethodOptionResolverInterface::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);

        $this->factory = new AuthorizeNetPaymentMethodFactory(
            $this->gateway,
            $this->requestStack,
            $this->methodOptionResolver,
            $this->eventDispatcher
        );
        $this->factory->setLogger($this->logger);
    }

    public function testCreate()
    {
        $config = $this->createMock(AuthorizeNetConfigInterface::class);

        $method = new AuthorizeNetPaymentMethod(
            $this->gateway,
            $config,
            $this->requestStack,
            $this->methodOptionResolver,
            $this->eventDispatcher
        );
        $method->setLogger($this->logger);

        $this->assertEquals($method, $this->factory->create($config));
    }
}
