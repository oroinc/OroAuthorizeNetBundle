<?php

namespace Oro\Bundle\AuthorizeNetBundle\Provider;

use Oro\Bundle\AuthorizeNetBundle\Entity\AuthorizeNetSettings;
use Oro\Bundle\AuthorizeNetBundle\Entity\Repository\AuthorizeNetSettingsRepository;
use Oro\Bundle\AuthorizeNetBundle\Integration\AuthorizeNetChannelType;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;

/**
 * Find integration channel for current website
 */
class IntegrationProvider
{
    /** @var WebsiteManager */
    protected $websiteManager;

    /** @var  DoctrineHelper */
    protected $doctrineHelper;

    /**
     * @param WebsiteManager $websiteManager
     * @param DoctrineHelper $doctrineHelper
     */
    public function __construct(WebsiteManager $websiteManager, DoctrineHelper $doctrineHelper)
    {
        $this->websiteManager = $websiteManager;
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * @param Website|null $website
     * @return Channel|null
     */
    public function getIntegration(Website $website = null)
    {
        if (!$website) {
            $website = $this->websiteManager->getCurrentWebsite();
        }
        /** @var AuthorizeNetSettingsRepository $settingsRepository */
        $settingsRepository = $this->doctrineHelper->getEntityRepository(AuthorizeNetSettings::class);
        /** @var AuthorizeNetSettings[] $transports */
        $transports =  $settingsRepository->findCIMEnabledSettingsByTypeAndWebsite(
            AuthorizeNetChannelType::TYPE,
            $website
        );

        $transport = reset($transports);

        return $transport ? $transport->getChannel() : null;
    }
}
