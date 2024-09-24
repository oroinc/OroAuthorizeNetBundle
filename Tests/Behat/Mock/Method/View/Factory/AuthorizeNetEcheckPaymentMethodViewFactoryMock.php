<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Behat\Mock\Method\View\Factory;

use Oro\Bundle\AuthorizeNetBundle\Method\Config\AuthorizeNetConfigInterface;
use Oro\Bundle\AuthorizeNetBundle\Method\View\Factory\AuthorizeNetEcheckPaymentMethodViewFactory;
use Oro\Bundle\AuthorizeNetBundle\Tests\Behat\Mock\Method\View\AuthorizeNetEcheckPaymentMethodViewMock;

class AuthorizeNetEcheckPaymentMethodViewFactoryMock extends AuthorizeNetEcheckPaymentMethodViewFactory
{
    #[\Override]
    public function create(AuthorizeNetConfigInterface $config)
    {
        return new AuthorizeNetEcheckPaymentMethodViewMock($this->formFactory, $this->tokenAccessor, $config);
    }
}
