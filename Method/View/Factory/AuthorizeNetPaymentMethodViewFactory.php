<?php

namespace Oro\Bundle\AuthorizeNetBundle\Method\View\Factory;

use Oro\Bundle\AuthorizeNetBundle\Method\Config\AuthorizeNetConfigInterface;
use Oro\Bundle\AuthorizeNetBundle\Method\View\AuthorizeNetPaymentMethodView;

/**
 * Authorize.Net payment method view factory
 */
class AuthorizeNetPaymentMethodViewFactory extends AbstractAuthorizeNetPaymentMethodViewFactory
{
    #[\Override]
    public function create(AuthorizeNetConfigInterface $config)
    {
        return new AuthorizeNetPaymentMethodView($this->formFactory, $this->tokenAccessor, $config);
    }
}
