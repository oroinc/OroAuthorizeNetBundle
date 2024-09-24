<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Behat\Mock\Method\Factory;

use Oro\Bundle\AuthorizeNetBundle\Method\Config\AuthorizeNetConfigInterface;
use Oro\Bundle\AuthorizeNetBundle\Method\Factory\AuthorizeNetPaymentMethodFactory;
use Oro\Bundle\AuthorizeNetBundle\Tests\Behat\Mock\Method\AuthorizeNetPaymentMethodMock;

class AuthorizeNetPaymentMethodFactoryMock extends AuthorizeNetPaymentMethodFactory
{
    #[\Override]
    public function create(AuthorizeNetConfigInterface $config)
    {
        $method = new AuthorizeNetPaymentMethodMock(
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
