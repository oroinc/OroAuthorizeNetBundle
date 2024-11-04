<?php

namespace Oro\Bundle\AuthorizeNetBundle\Method\View\Factory;

use Oro\Bundle\AuthorizeNetBundle\Method\Config\AuthorizeNetConfigInterface;
use Oro\Bundle\AuthorizeNetBundle\Method\View\AuthorizeNetEcheckPaymentMethodView;

/**
 * Authorize.Net eCheck payment method view factory
 */
class AuthorizeNetEcheckPaymentMethodViewFactory extends AbstractAuthorizeNetPaymentMethodViewFactory
{
    #[\Override]
    public function create(AuthorizeNetConfigInterface $config)
    {
        return new AuthorizeNetEcheckPaymentMethodView($this->formFactory, $this->tokenAccessor, $config);
    }
}
