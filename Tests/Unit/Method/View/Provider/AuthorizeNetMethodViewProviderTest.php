<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\Method\View\Provider;

use Oro\Bundle\AuthorizeNetBundle\Method\Config\AuthorizeNetConfigInterface;
use Oro\Bundle\AuthorizeNetBundle\Method\Config\Provider\AuthorizeNetConfigProviderInterface;
use Oro\Bundle\AuthorizeNetBundle\Method\View\Factory\AuthorizeNetPaymentMethodViewFactoryInterface;
use Oro\Bundle\AuthorizeNetBundle\Method\View\Provider\AuthorizeNetMethodViewProvider;
use Oro\Bundle\PaymentBundle\Tests\Unit\Method\View\Provider\AbstractMethodViewProviderTest;

class AuthorizeNetMethodViewProviderTest extends AbstractMethodViewProviderTest
{
    #[\Override]
    protected function setUp(): void
    {
        $this->factory = $this->createMock(AuthorizeNetPaymentMethodViewFactoryInterface::class);
        $this->configProvider = $this->createMock(AuthorizeNetConfigProviderInterface::class);
        $this->paymentConfigClass = AuthorizeNetConfigInterface::class;
        $this->provider = new AuthorizeNetMethodViewProvider($this->factory, $this->configProvider);
    }
}
