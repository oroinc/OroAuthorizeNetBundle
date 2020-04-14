<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\Method\View\Factory;

use Oro\Bundle\AuthorizeNetBundle\Method\Config\AuthorizeNetConfigInterface;
use Oro\Bundle\AuthorizeNetBundle\Method\View\AuthorizeNetPaymentMethodView;
use Oro\Bundle\AuthorizeNetBundle\Method\View\Factory\AuthorizeNetPaymentMethodViewFactory;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Symfony\Component\Form\FormFactoryInterface;

class AuthorizeNetPaymentMethodViewFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var AuthorizeNetPaymentMethodViewFactory
     */
    protected $factory;

    /**
     * @var TokenAccessorInterface
     */
    protected $tokenAccessor;

    protected function setUp(): void
    {
        $this->formFactory = $this->createMock(FormFactoryInterface::class);
        $this->tokenAccessor = $this->createMock(TokenAccessorInterface::class);
        $this->factory = new AuthorizeNetPaymentMethodViewFactory($this->formFactory, $this->tokenAccessor);
    }

    public function testCreate()
    {
        /** @var AuthorizeNetConfigInterface $config */
        $config = $this->createMock(AuthorizeNetConfigInterface::class);

        $expectedView = new AuthorizeNetPaymentMethodView($this->formFactory, $this->tokenAccessor, $config);

        $this->assertEquals($expectedView, $this->factory->create($config));
    }
}
