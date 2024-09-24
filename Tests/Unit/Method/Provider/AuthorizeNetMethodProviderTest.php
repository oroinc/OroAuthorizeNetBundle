<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\Method\Provider;

use Oro\Bundle\AuthorizeNetBundle\Method\Config\AuthorizeNetConfigInterface;
use Oro\Bundle\AuthorizeNetBundle\Method\Config\Provider\AuthorizeNetConfigProviderInterface;
use Oro\Bundle\AuthorizeNetBundle\Method\Factory\AuthorizeNetPaymentMethodFactoryInterface;
use Oro\Bundle\AuthorizeNetBundle\Method\Provider\AuthorizeNetMethodProvider;
use Oro\Bundle\PaymentBundle\Tests\Unit\Method\Provider\AbstractMethodProviderTest;

class AuthorizeNetMethodProviderTest extends AbstractMethodProviderTest
{
    #[\Override]
    protected function setUp(): void
    {
        $this->factory = $this->createMock(AuthorizeNetPaymentMethodFactoryInterface::class);
        $this->configProvider = $this->createMock(AuthorizeNetConfigProviderInterface::class);
        $this->paymentConfigClass = AuthorizeNetConfigInterface::class;
        $this->methodProvider = new AuthorizeNetMethodProvider($this->configProvider, $this->factory);
    }
}
