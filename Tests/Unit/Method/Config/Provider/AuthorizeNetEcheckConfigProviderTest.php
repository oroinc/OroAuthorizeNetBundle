<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\Method\Config\Provider;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\AuthorizeNetBundle\Entity\AuthorizeNetSettings;
use Oro\Bundle\AuthorizeNetBundle\Entity\Repository\AuthorizeNetSettingsRepository;
use Oro\Bundle\AuthorizeNetBundle\Integration\AuthorizeNetChannelType;
use Oro\Bundle\AuthorizeNetBundle\Method\Config\AuthorizeNetConfig;
use Oro\Bundle\AuthorizeNetBundle\Method\Config\Factory\AuthorizeNetConfigFactory;
use Oro\Bundle\AuthorizeNetBundle\Method\Config\Provider\AuthorizeNetEcheckConfigProvider;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Component\Testing\Unit\EntityTrait;
use Psr\Log\LoggerInterface;

class AuthorizeNetEcheckConfigProviderTest extends \PHPUnit\Framework\TestCase
{
    use EntityTrait;

    /** @var AuthorizeNetSettings[] */
    private $settings;

    /** @var AuthorizeNetEcheckConfigProvider */
    private $configProvider;

    #[\Override]
    protected function setUp(): void
    {
        $channel1 = $this->getEntity(Channel::class, ['id' => 1, 'type' => AuthorizeNetChannelType::TYPE]);
        $channel2 = $this->getEntity(Channel::class, ['id' => 2, 'type' => AuthorizeNetChannelType::TYPE]);

        $this->settings[] = $this->getEntity(AuthorizeNetSettings::class, [
            'id' => 1,
            'channel' => $channel1,
            'echeck_enabled' => true
        ]);
        $this->settings[] = $this->getEntity(AuthorizeNetSettings::class, [
            'id' => 2,
            'channel' => $channel2,
            'echeck_enabled' => false
        ]);

        $config = $this->createMock(AuthorizeNetConfig::class);
        $config->expects($this->once())
            ->method('getPaymentMethodIdentifier')
            ->willReturn('authorize_net_echeck_1');

        $objectRepository = $this->createMock(AuthorizeNetSettingsRepository::class);
        $objectRepository->expects($this->once())
            ->method('getEnabledSettingsByType')
            ->with(AuthorizeNetChannelType::TYPE)
            ->willReturn($this->settings);

        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->expects($this->once())
            ->method('getRepository')
            ->willReturn($objectRepository);

        $doctrine = $this->createMock(ManagerRegistry::class);
        $doctrine->expects($this->once())
            ->method('getManagerForClass')
            ->willReturn($objectManager);

        $factory = $this->createMock(AuthorizeNetConfigFactory::class);
        $factory->expects($this->once())
            ->method('createConfig')
            ->willReturn($config);

        $this->configProvider = new AuthorizeNetEcheckConfigProvider(
            $doctrine,
            $this->createMock(LoggerInterface::class),
            $factory
        );
    }

    public function testGetPaymentConfigs()
    {
        $this->assertCount(1, $this->configProvider->getPaymentConfigs());
    }

    public function testGetPaymentConfig()
    {
        $identifier = 'authorize_net_echeck_1';

        $this->assertInstanceOf(
            AuthorizeNetConfig::class,
            $this->configProvider->getPaymentConfig($identifier)
        );
    }

    public function testHasPaymentConfig()
    {
        $identifier = 'authorize_net_echeck_1';

        $this->assertTrue($this->configProvider->hasPaymentConfig($identifier));
    }
}
