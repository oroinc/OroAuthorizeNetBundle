<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\EventListener;

use Knp\Menu\MenuFactory;
use Knp\Menu\MenuItem;
use Oro\Bundle\AuthorizeNetBundle\EventListener\NavigationListener;
use Oro\Bundle\AuthorizeNetBundle\Method\Config\Provider\AuthorizeNetCIMEnabledConfigProvider;
use Oro\Bundle\NavigationBundle\Event\ConfigureMenuEvent;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;

class NavigationListenerTest extends \PHPUnit\Framework\TestCase
{
    /** @var AuthorizeNetCIMEnabledConfigProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $configProvider;

    /** @var WebsiteManager|\PHPUnit\Framework\MockObject\MockObject */
    private $websiteManager;

    /** @var NavigationListener */
    private $listener;

    protected function setUp(): void
    {
        $this->configProvider = $this->createMock(AuthorizeNetCIMEnabledConfigProvider::class);
        $this->websiteManager =  $this->createMock(WebsiteManager::class);
        $this->listener = new NavigationListener($this->websiteManager, $this->configProvider);
    }

    /**
     * @dataProvider onNavigationConfigureDataProvider
     * @param $cimEnabled
     * @param $expectedIsDisplayed
     */
    public function testOnNavigationConfigure(bool $cimEnabled, bool $expectedIsDisplayed)
    {
        $this
            ->websiteManager
            ->expects($this->once())
            ->method('getCurrentWebsite')
            ->willReturn(new Website());

        $this
            ->configProvider
            ->expects($this->atLeastOnce())
            ->method('hasPaymentWithEnabledCIMByWebsite')
            ->willReturn($cimEnabled);

        $factory = new MenuFactory();
        $menu = new MenuItem('oro_customer_menu', $factory);
        $menuItem = new MenuItem(NavigationListener::MENU_ITEM_ID, $factory);
        $menu->addChild($menuItem);

        $event = new ConfigureMenuEvent($factory, $menu);
        $this->listener->onNavigationConfigure($event);

        $this->assertEquals($expectedIsDisplayed, $menuItem->isDisplayed());
    }

    public function onNavigationConfigureDataProvider()
    {
        return [
            'cim enabled' => [
                'cimEnabled' => true,
                'expectedIsDisplayed' => true
             ],
            'cim disabled' => [
                'cimEnabled' => false,
                'expectedIsDisplayed' => false
            ]
        ];
    }
}
