<?php

namespace Oro\Bundle\AuthorizeNetBundle\Method\Config\Factory;

use Oro\Bundle\AuthorizeNetBundle\Entity\AuthorizeNetSettings;
use Oro\Bundle\AuthorizeNetBundle\Method\Config\AuthorizeNetConfigInterface;

/**
 * Interface for config factory for Authorize.Net payment method
 */
interface AuthorizeNetConfigFactoryInterface
{
    /**
     * @param AuthorizeNetSettings $settings
     * @return AuthorizeNetConfigInterface
     */
    public function createConfig(AuthorizeNetSettings $settings);
}
