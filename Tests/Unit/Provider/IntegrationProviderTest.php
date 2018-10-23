<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\Provider;

use Oro\Bundle\AuthorizeNetBundle\Entity\AuthorizeNetSettings;
use Oro\Bundle\AuthorizeNetBundle\Entity\Repository\AuthorizeNetSettingsRepository;
use Oro\Bundle\AuthorizeNetBundle\Integration\AuthorizeNetChannelType;
use Oro\Bundle\AuthorizeNetBundle\Provider\IntegrationProvider;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;

class IntegrationProviderTest extends \PHPUnit\Framework\TestCase
{
    /** @var WebsiteManager|\PHPUnit\Framework\MockObject\MockObject */
    private $websiteManager;

    /** @var  DoctrineHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $doctrineHelper;

    /** @var  AuthorizeNetSettingsRepository|\PHPUnit\Framework\MockObject\MockObject */
    private $repository;

    /** @var IntegrationProvider */
    private $provider;

    protected function setUp()
    {
        $this->doctrineHelper = $this->createMock(DoctrineHelper::class);
        $this->websiteManager = $this->createMock(WebsiteManager::class);
        $this->repository = $this->createMock(AuthorizeNetSettingsRepository::class);

        $this->provider = new IntegrationProvider($this->websiteManager, $this->doctrineHelper);
    }

    public function testGetIntegrationByCurrentWebsite()
    {
        $website = new Website();
        $transport = new AuthorizeNetSettings();
        $transport->setChannel(new Channel());

        $this->repository
            ->expects($this->once())
            ->method('findCIMEnabledSettingsByTypeAndWebsite')
            ->with(AuthorizeNetChannelType::TYPE, $website)
            ->willReturn([$transport]);

        $this->websiteManager
            ->expects($this->once())
            ->method('getCurrentWebsite')
            ->willReturn($website);

        $this->doctrineHelper
            ->expects($this->once())
            ->method('getEntityRepository')
            ->with(AuthorizeNetSettings::class)
            ->willReturn($this->repository);

        $integration = $this->provider->getIntegration();
        $this->assertEquals($transport->getChannel(), $integration);
    }

    public function testGetIntegrationBySpecificWebsite()
    {
        $website = new Website();
        $transport = new AuthorizeNetSettings();
        $transport->setChannel(new Channel());

        $this->repository
            ->expects($this->once())
            ->method('findCIMEnabledSettingsByTypeAndWebsite')
            ->with(AuthorizeNetChannelType::TYPE, $website)
            ->willReturn([$transport]);

        $this->websiteManager
            ->expects($this->never())
            ->method('getCurrentWebsite');

        $this->doctrineHelper
            ->expects($this->once())
            ->method('getEntityRepository')
            ->with(AuthorizeNetSettings::class)
            ->willReturn($this->repository);

        $integration = $this->provider->getIntegration($website);
        $this->assertEquals($transport->getChannel(), $integration);
    }

    public function testGetIntegrationNoIntegrationFound()
    {
        $website = new Website();

        $this->repository
            ->expects($this->once())
            ->method('findCIMEnabledSettingsByTypeAndWebsite')
            ->with(AuthorizeNetChannelType::TYPE, $website)
            ->willReturn([]);

        $this->websiteManager
            ->expects($this->never())
            ->method('getCurrentWebsite');

        $this->doctrineHelper
            ->expects($this->once())
            ->method('getEntityRepository')
            ->with(AuthorizeNetSettings::class)
            ->willReturn($this->repository);

        $integration = $this->provider->getIntegration($website);
        $this->assertNull($integration);
    }
}
