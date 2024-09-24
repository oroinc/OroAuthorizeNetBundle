<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\Method\View\Factory;

use Oro\Bundle\AuthorizeNetBundle\Method\Config\AuthorizeNetConfigInterface;
use Oro\Bundle\AuthorizeNetBundle\Method\View\AuthorizeNetEcheckPaymentMethodView;
use Oro\Bundle\AuthorizeNetBundle\Method\View\Factory\AuthorizeNetEcheckPaymentMethodViewFactory;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Symfony\Component\Form\FormFactoryInterface;

class AuthorizeNetEcheckPaymentMethodViewFactoryTest extends \PHPUnit\Framework\TestCase
{
    /** @var FormFactoryInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $formFactory;

    /** @var TokenAccessorInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $tokenAccessor;

    /** @var AuthorizeNetEcheckPaymentMethodViewFactory */
    private $factory;

    #[\Override]
    protected function setUp(): void
    {
        $this->formFactory = $this->createMock(FormFactoryInterface::class);
        $this->tokenAccessor = $this->createMock(TokenAccessorInterface::class);

        $this->factory = new AuthorizeNetEcheckPaymentMethodViewFactory($this->formFactory, $this->tokenAccessor);
    }

    public function testCreate()
    {
        $config = $this->createMock(AuthorizeNetConfigInterface::class);

        $expectedView = new AuthorizeNetEcheckPaymentMethodView($this->formFactory, $this->tokenAccessor, $config);

        $this->assertEquals($expectedView, $this->factory->create($config));
    }
}
