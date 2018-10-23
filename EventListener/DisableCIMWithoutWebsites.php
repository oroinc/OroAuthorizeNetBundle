<?php

namespace Oro\Bundle\AuthorizeNetBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Oro\Bundle\AuthorizeNetBundle\Entity\AuthorizeNetSettings;
use Oro\Bundle\AuthorizeNetBundle\Entity\Repository\AuthorizeNetSettingsRepository;
use Oro\Bundle\AuthorizeNetBundle\Integration\AuthorizeNetChannelType;
use Oro\Bundle\WebsiteBundle\Entity\Website;

/**
 * This functionality disable CIM payment setting of payment method
 * in case when last enabled CIM website is going to be removed.
 */
class DisableCIMWithoutWebsites
{
    /**
     * @param Website $website
     * @param LifecycleEventArgs $event
     */
    public function preRemove(Website $website, LifecycleEventArgs $event)
    {
        $em = $event->getEntityManager();

        /** @var AuthorizeNetSettingsRepository $repository */
        $repository = $em->getRepository(AuthorizeNetSettings::class);
        $settings = $repository->findCIMEnabledSettingsByTypeAndWebsite(AuthorizeNetChannelType::TYPE, $website);

        if (empty($settings)) {
            return;
        }

        foreach ($settings as $setting) {
            $setting->getEnabledCIMWebsites()->removeElement($website);
            if ($setting->getEnabledCIMWebsites()->isEmpty()) {
                $setting->setEnabledCIM(false);
            }
        }
    }
}
