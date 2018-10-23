<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\Method\View\Factory;

use Oro\Bundle\AuthorizeNetBundle\Method\Config\AuthorizeNetConfigInterface;
use Oro\Bundle\AuthorizeNetBundle\Method\View\AuthorizeNetEcheckPaymentMethodView;
use Oro\Bundle\AuthorizeNetBundle\Method\View\Factory\AuthorizeNetEcheckPaymentMethodViewFactory;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Symfony\Component\Form\FormFactoryInterface;

class AuthorizeNetEcheckPaymentMethodViewFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var AuthorizeNetEcheckPaymentMethodViewFactory
     */
    protected $factory;

    /**
     * @var TokenAccessorInterface
     */
    protected $tokenAccessor;

    protected function setUp()
    {
        $this->formFactory = $this->createMock(FormFactoryInterface::class);
        $this->tokenAccessor = $this->createMock(TokenAccessorInterface::class);
        $this->factory = new AuthorizeNetEcheckPaymentMethodViewFactory($this->formFactory, $this->tokenAccessor);
    }

    public function testCreate()
    {
        /** @var AuthorizeNetConfigInterface $config */
        $config = $this->createMock(AuthorizeNetConfigInterface::class);

        $expectedView = new AuthorizeNetEcheckPaymentMethodView($this->formFactory, $this->tokenAccessor, $config);

        $this->assertEquals($expectedView, $this->factory->create($config));
    }
}
