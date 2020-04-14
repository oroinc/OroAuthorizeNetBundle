<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\EventListener;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Oro\Bundle\AuthorizeNetBundle\Entity\AuthorizeNetSettings;
use Oro\Bundle\AuthorizeNetBundle\Entity\Repository\AuthorizeNetSettingsRepository;
use Oro\Bundle\AuthorizeNetBundle\EventListener\DisableCIMWithoutWebsites;
use Oro\Bundle\AuthorizeNetBundle\Integration\AuthorizeNetChannelType;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Component\Testing\Unit\EntityTrait;

class DisableCIMWithoutWebsitesTest extends \PHPUnit\Framework\TestCase
{
    use EntityTrait;

    /** @var DisableCIMWithoutWebsites */
    private $eventListener;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->eventListener = new DisableCIMWithoutWebsites();
    }

    /**
     * @dataProvider preRemoveDataProvider
     * @param Website $website
     * @param AuthorizeNetSettings $settings
     * @param bool $expectedEnabledCIM
     */
    public function testPreRemove(Website $website, AuthorizeNetSettings $settings, bool $expectedEnabledCIM)
    {
        $repository = $this->createMock(AuthorizeNetSettingsRepository::class);
        $repository->expects($this->once())
            ->method('findCIMEnabledSettingsByTypeAndWebsite')
            ->with(AuthorizeNetChannelType::TYPE, $website)
            ->willReturn([$settings]);

        $em = $this->createMock(EntityManager::class);
        $em->expects($this->once())
            ->method('getRepository')
            ->with(AuthorizeNetSettings::class)
            ->willReturn($repository);

        $event = new LifecycleEventArgs($website, $em);

        $this->eventListener->preRemove($website, $event);

        $this->assertSame($expectedEnabledCIM, $settings->isEnabledCIM());
    }

    public function testPreRemoveNoApplicableSettings()
    {
        $website = $this->getEntity(Website::class, ['id' => 1]);
        $repository = $this->createMock(AuthorizeNetSettingsRepository::class);
        $repository->expects($this->once())
            ->method('findCIMEnabledSettingsByTypeAndWebsite')
            ->with(AuthorizeNetChannelType::TYPE, $website)
            ->willReturn([]);

        $em = $this->createMock(EntityManager::class);
        $em->expects($this->once())
            ->method('getRepository')
            ->with(AuthorizeNetSettings::class)
            ->willReturn($repository);

        $event = new LifecycleEventArgs($website, $em);

        $this->eventListener->preRemove($website, $event);
    }

    /**
     * @return array
     */
    public function preRemoveDataProvider()
    {
        $searchWebsite = $this->getEntity(Website::class, ['id' => 1]);
        return [
            'one website (disable CIM)' => [
                'website' => $searchWebsite,
                'settings' => $this->getEntity(
                    AuthorizeNetSettings::class,
                    [
                        'enabledCIM' => true,
                        'enabledCIMWebsites' => new ArrayCollection([$searchWebsite])
                    ]
                ),
                'expectedEnabledCIM' => false
            ],
            'two websites (nothing to process)' => [
                'website' => $searchWebsite,
                'settings' => $this->getEntity(
                    AuthorizeNetSettings::class,
                    [
                        'enabledCIM' => true,
                        'enabledCIMWebsites' => new ArrayCollection(
                            [
                                $searchWebsite,
                                $this->getEntity(Website::class, ['id' => 2])
                            ]
                        )
                    ]
                ),
                'expectedEnabledCIM' => true
            ]
        ];
    }
}
