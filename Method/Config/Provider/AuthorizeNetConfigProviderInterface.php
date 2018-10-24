<?php

namespace Oro\Bundle\AuthorizeNetBundle\Method\Config\Provider;

use Oro\Bundle\AuthorizeNetBundle\Method\Config\AuthorizeNetConfigInterface;

/**
 * Interface for config provider of Authorize.Net payment method
 */
interface AuthorizeNetConfigProviderInterface
{
    /**
     * @return AuthorizeNetConfigInterface[]
     */
    public function getPaymentConfigs();

    /**
     * @param string $identifier
     * @return AuthorizeNetConfigInterface|null
     */
    public function getPaymentConfig($identifier);

    /**
     * @param string $identifier
     * @return bool
     */
    public function hasPaymentConfig($identifier);
}
