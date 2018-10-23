<?php

namespace Oro\Bundle\AuthorizeNetBundle\Method\View\Factory;

use Oro\Bundle\AuthorizeNetBundle\Method\Config\AuthorizeNetConfigInterface;
use Oro\Bundle\PaymentBundle\Method\View\PaymentMethodViewInterface;

/**
 * Authorize.Net payment method view provider factory interface
 */
interface AuthorizeNetPaymentMethodViewFactoryInterface
{
    /**
     * @param AuthorizeNetConfigInterface $config
     * @return PaymentMethodViewInterface
     */
    public function create(AuthorizeNetConfigInterface $config);
}
