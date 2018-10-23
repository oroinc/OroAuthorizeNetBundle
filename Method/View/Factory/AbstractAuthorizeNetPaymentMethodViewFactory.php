<?php

namespace Oro\Bundle\AuthorizeNetBundle\Method\View\Factory;

use Oro\Bundle\AuthorizeNetBundle\Method\Config\AuthorizeNetConfigInterface;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * Authorize.Net abstract payment method view factory
 */
abstract class AbstractAuthorizeNetPaymentMethodViewFactory implements AuthorizeNetPaymentMethodViewFactoryInterface
{
    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var TokenAccessorInterface
     */
    protected $tokenAccessor;

    /**
     * @param FormFactoryInterface $formFactory
     * @param TokenAccessorInterface $tokenAccessor
     */
    public function __construct(FormFactoryInterface $formFactory, TokenAccessorInterface $tokenAccessor)
    {
        $this->formFactory = $formFactory;
        $this->tokenAccessor = $tokenAccessor;
    }

    /**
     * {@inheritdoc}
     */
    abstract public function create(AuthorizeNetConfigInterface $config);
}
