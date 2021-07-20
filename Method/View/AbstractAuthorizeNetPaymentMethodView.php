<?php

namespace Oro\Bundle\AuthorizeNetBundle\Method\View;

use Oro\Bundle\AuthorizeNetBundle\Method\Config\AuthorizeNetConfigInterface;
use Oro\Bundle\PaymentBundle\Context\PaymentContextInterface;
use Oro\Bundle\PaymentBundle\Method\View\PaymentMethodViewInterface;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * Abstract payment method view
 */
abstract class AbstractAuthorizeNetPaymentMethodView implements PaymentMethodViewInterface
{
    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var AuthorizeNetConfigInterface
     */
    protected $config;

    /**
     * @var TokenAccessorInterface
     */
    protected $tokenAccessor;

    public function __construct(
        FormFactoryInterface $formFactory,
        TokenAccessorInterface $tokenAccessor,
        AuthorizeNetConfigInterface $config
    ) {
        $this->formFactory = $formFactory;
        $this->tokenAccessor = $tokenAccessor;
        $this->config = $config;
    }

    /**
     * {@inheritDoc}
     */
    abstract public function getOptions(PaymentContextInterface $context);

    /**
     * {@inheritDoc}
     */
    public function getBlock()
    {
        return '_payment_methods_authorize_net_widget';
    }

    /**
     * {@inheritDoc}
     */
    public function getLabel()
    {
        return $this->config->getLabel();
    }

    /**
     * {@inheritDoc}
     */
    public function getShortLabel()
    {
        return $this->config->getShortLabel();
    }

    /**
     * {@inheritDoc}
     */
    public function getAdminLabel()
    {
        return $this->config->getAdminLabel();
    }

    /**
     * {@inheritDoc}
     */
    public function getPaymentMethodIdentifier()
    {
        return $this->config->getPaymentMethodIdentifier();
    }

    /**
     * @return bool
     */
    protected function isCIMEnabled()
    {
        return $this->tokenAccessor->hasUser() && $this->config->isEnabledCIM();
    }
}
