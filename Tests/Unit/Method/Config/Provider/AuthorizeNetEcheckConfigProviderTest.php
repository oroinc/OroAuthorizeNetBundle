<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\Method\Config\Provider;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
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

    /**
     * @var ManagerRegistry|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $doctrine;

    /**
     * @var AuthorizeNetSettings[]
     */
    protected $settings;

    /**
     * @var AuthorizeNetEcheckConfigProvider
     */
    protected $configProvider;

    protected function setUp()
    {
        $channelType = AuthorizeNetChannelType::TYPE;

        $channel1 = $this->getEntity(Channel::class, ['id' => 1, 'type' => $channelType]);
        $channel2 = $this->getEntity(Channel::class, ['id' => 2, 'type' => $channelType]);

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
        $config->expects($this->at(0))
            ->method('getPaymentMethodIdentifier')
            ->willReturn('authorize_net_echeck_1');

        $this->doctrine = $this->createMock(ManagerRegistry::class);

        $objectRepository = $this->createMock(AuthorizeNetSettingsRepository::class);
        $objectRepository->expects($this->once())
            ->method('getEnabledSettingsByType')
            ->with($channelType)
            ->willReturn($this->settings);

        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->expects($this->once())->method('getRepository')->willReturn($objectRepository);

        $this->doctrine->expects($this->once())->method('getManagerForClass')->willReturn($objectManager);

        /** @var AuthorizeNetConfigFactory|\PHPUnit\Framework\MockObject\MockObject $factory */
        $factory = $this->createMock(AuthorizeNetConfigFactory::class);
        $factory->expects($this->exactly(1))
            ->method('createConfig')
            ->willReturn($config);

        /** @var LoggerInterface|\PHPUnit\Framework\MockObject\MockObject $logger */
        $logger = $this->createMock(LoggerInterface::class);

        $this->configProvider = new AuthorizeNetEcheckConfigProvider(
            $this->doctrine,
            $logger,
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
