<?php

namespace Oro\Bundle\AuthorizeNetBundle\EventListener;

use Oro\Bundle\AuthorizeNetBundle\Method\Config\Provider\AuthorizeNetCIMEnabledConfigProvider;
use Oro\Bundle\NavigationBundle\Event\ConfigureMenuEvent;
use Oro\Bundle\NavigationBundle\Utils\MenuUpdateUtils;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;

/**
 * Show/Hide menu item depending on settings
 */
class NavigationListener
{
    const MENU_ITEM_ID = 'oro_authorize_net_payment_profile_frontend_index';

    /** @var WebsiteManager */
    private $websiteManager;

    /** @var AuthorizeNetCIMEnabledConfigProvider */
    private $configProvider;

    public function __construct(WebsiteManager $websiteManager, AuthorizeNetCIMEnabledConfigProvider $configProvider)
    {
        $this->websiteManager = $websiteManager;
        $this->configProvider = $configProvider;
    }

    public function onNavigationConfigure(ConfigureMenuEvent $event)
    {
        $menuItem = MenuUpdateUtils::findMenuItem($event->getMenu(), self::MENU_ITEM_ID);
        if ($menuItem !== null) {
            $display = false;
            $currentWebsite = $this->websiteManager->getCurrentWebsite();
            if ($currentWebsite && $this->configProvider->hasPaymentWithEnabledCIMByWebsite($currentWebsite)) {
                $display = true;
            }
            $menuItem->setDisplay($display);
        }
    }
}
