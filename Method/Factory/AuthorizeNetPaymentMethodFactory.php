<?php

namespace Oro\Bundle\AuthorizeNetBundle\Method\Factory;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Gateway;
use Oro\Bundle\AuthorizeNetBundle\Method\AuthorizeNetPaymentMethod;
use Oro\Bundle\AuthorizeNetBundle\Method\Config\AuthorizeNetConfigInterface;
use Oro\Bundle\AuthorizeNetBundle\Method\Option\Resolver\MethodOptionResolverInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Factory allows to create AuthorizeNet payment method, using
 * dynamic property AuthorizeNetConfigInterface $config
 * AuthorizeNetPaymentMethod depends on this dynamic evaluated config
 * so it can not be defined directly in di and requires factory to create it
 */
class AuthorizeNetPaymentMethodFactory implements AuthorizeNetPaymentMethodFactoryInterface
{
    use LoggerAwareTrait;

    /** @var Gateway */
    protected $gateway;

    /** @var RequestStack */
    protected $requestStack;

    /** @var MethodOptionResolverInterface */
    protected $methodOptionResolver;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    public function __construct(
        Gateway $gateway,
        RequestStack $requestStack,
        MethodOptionResolverInterface $methodOptionResolver,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->gateway = $gateway;
        $this->requestStack = $requestStack;
        $this->methodOptionResolver = $methodOptionResolver;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function create(AuthorizeNetConfigInterface $config)
    {
        $method = new AuthorizeNetPaymentMethod(
            $this->gateway,
            $config,
            $this->requestStack,
            $this->methodOptionResolver,
            $this->eventDispatcher
        );

        if ($this->logger) {
            $method->setLogger($this->logger);
        }

        return $method;
    }
}
