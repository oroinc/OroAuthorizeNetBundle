<?php

namespace Oro\Bundle\AuthorizeNetBundle\Provider;

use Oro\Bundle\AuthorizeNetBundle\Method\Config\AuthorizeNetConfigInterface;
use Oro\Bundle\AuthorizeNetBundle\Method\Config\Provider\AuthorizeNetCIMEnabledConfigProviderInterface;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;

/**
 * Find integration config by current website
 */
class CIMEnabledIntegrationConfigProvider
{
    /** @var WebsiteManager */
    private $websiteManager;

    /** @var AuthorizeNetCIMEnabledConfigProviderInterface */
    private $configProvider;

    /** @var AuthorizeNetConfigInterface */
    private $config;

    /**
     * @param AuthorizeNetCIMEnabledConfigProviderInterface $configProvider
     * @param WebsiteManager $websiteManager
     */
    public function __construct(
        AuthorizeNetCIMEnabledConfigProviderInterface $configProvider,
        WebsiteManager $websiteManager
    ) {
        $this->configProvider = $configProvider;
        $this->websiteManager = $websiteManager;
    }

    /**
     * @return AuthorizeNetConfigInterface
     */
    public function getConfig()
    {
        if (!$this->config) {
            $this->config = $this->configProvider->getPaymentConfigWithEnabledCIMByWebsite(
                $this->websiteManager->getCurrentWebsite()
            );
        }

        return $this->config;
    }
}
