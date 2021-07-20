<?php

namespace Oro\Bundle\AuthorizeNetBundle\Method\Config\Provider;

use Oro\Bundle\AuthorizeNetBundle\Method\Config\AuthorizeNetConfigInterface;
use Oro\Bundle\WebsiteBundle\Entity\Website;

/**
 * Interface for config provider of Authorize.Net payment method
 * Search CIM enabled config by website
 */
interface AuthorizeNetCIMEnabledConfigProviderInterface
{
    /**
     * @param Website $website
     *
     * @return bool
     */
    public function hasPaymentWithEnabledCIMByWebsite(Website $website);

    /**
     * @param Website $website
     *
     * @return null|AuthorizeNetConfigInterface
     */
    public function getPaymentConfigWithEnabledCIMByWebsite(Website $website);
}
