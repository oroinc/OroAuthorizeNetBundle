<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\Provider;

use Oro\Bundle\AuthorizeNetBundle\Entity\Repository\AuthorizeNetSettingsRepository;
use Oro\Bundle\AuthorizeNetBundle\Method\Config\AuthorizeNetConfig;
use Oro\Bundle\AuthorizeNetBundle\Method\Config\Provider\AuthorizeNetCIMEnabledConfigProviderInterface;
use Oro\Bundle\AuthorizeNetBundle\Provider\CIMEnabledIntegrationConfigProvider;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;

class CIMEnabledIntegrationConfigProviderTest extends \PHPUnit\Framework\TestCase
{
    /** @var WebsiteManager|\PHPUnit\Framework\MockObject\MockObject */
    private $websiteManager;

    /** @var  AuthorizeNetCIMEnabledConfigProviderInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $configProvider;

    /** @var  AuthorizeNetSettingsRepository|\PHPUnit\Framework\MockObject\MockObject */
    private $repository;

    /** @var CIMEnabledIntegrationConfigProvider */
    private $provider;

    protected function setUp()
    {
        $this->configProvider = $this->createMock(AuthorizeNetCIMEnabledConfigProviderInterface::class);
        $this->websiteManager = $this->createMock(WebsiteManager::class);
        $this->repository = $this->createMock(AuthorizeNetSettingsRepository::class);

        $this->provider = new CIMEnabledIntegrationConfigProvider($this->configProvider, $this->websiteManager);
    }

    public function testGetConfig()
    {
        $website = new Website();
        $config = new AuthorizeNetConfig();

        $this->websiteManager
            ->expects($this->once())
            ->method('getCurrentWebsite')
            ->willReturn($website);

        $this->configProvider
            ->expects($this->once())
            ->method('getPaymentConfigWithEnabledCIMByWebsite')
            ->with($website)
            ->willReturn($config);

        $actualConfig = $this->provider->getConfig();
        $this->assertEquals($config, $actualConfig);

        $secondCallConfig = $this->provider->getConfig();
        $this->assertEquals($config, $secondCallConfig);
    }
}
