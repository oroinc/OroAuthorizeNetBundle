<?php

namespace Oro\Bundle\AuthorizeNetBundle\Method\Config\Provider;

use Oro\Bundle\AuthorizeNetBundle\Method\Config\AuthorizeNetConfigInterface;
use Oro\Bundle\WebsiteBundle\Entity\Website;

/**
 * Contains methods that help to check website on enabled CIM functionality and
 * get config of payment method with enabled CIM functionality.
 */
class AuthorizeNetCIMEnabledConfigProvider implements AuthorizeNetCIMEnabledConfigProviderInterface
{
    /** @var AuthorizeNetConfigProviderInterface */
    private $configProvider;

    public function __construct(AuthorizeNetConfigProviderInterface $configProvider)
    {
        $this->configProvider = $configProvider;
    }

    #[\Override]
    public function hasPaymentWithEnabledCIMByWebsite(Website $website)
    {
        return $this->getPaymentConfigWithEnabledCIMByWebsite($website) instanceof AuthorizeNetConfigInterface;
    }

    #[\Override]
    public function getPaymentConfigWithEnabledCIMByWebsite(Website $website)
    {
        $paymentConfigs = $this->configProvider->getPaymentConfigs();
        $filteredPaymentConfigs = array_filter(
            $paymentConfigs,
            function (AuthorizeNetConfigInterface $paymentConfig) use ($website) {
                return $paymentConfig->isEnabledCIM() &&
                    false !== $paymentConfig->getEnabledCIMWebsites()->indexOf($website);
            }
        );

        if (empty($filteredPaymentConfigs)) {
            return null;
        }

        /**
         * We sure that only one config exist per-website,
         * because this situation filters ForbidToReuseEnabledCIMWebsitesValidator
         */
        return reset($filteredPaymentConfigs);
    }
}
