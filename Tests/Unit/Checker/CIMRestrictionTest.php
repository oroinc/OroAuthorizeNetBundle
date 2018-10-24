<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\Checker;

use Oro\Bundle\AuthorizeNetBundle\Checker\CIMRestriction;
use Oro\Bundle\AuthorizeNetBundle\Entity\AuthorizeNetSettings;
use Oro\Bundle\AuthorizeNetBundle\Entity\Repository\AuthorizeNetSettingsRepository;
use Oro\Bundle\AuthorizeNetBundle\Integration\AuthorizeNetChannelType;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Component\Testing\Unit\EntityTrait;

class CIMRestrictionTest extends \PHPUnit\Framework\TestCase
{
    use EntityTrait;

    /** @var CIMRestriction */
    private $checker;

    /** @var AuthorizeNetSettingsRepository | \PHPUnit\Framework\MockObject\MockObject */
    private $authorizeNetSettingsRepository;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->authorizeNetSettingsRepository = $this->createMock(AuthorizeNetSettingsRepository::class);
        /** @var DoctrineHelper|\PHPUnit\Framework\MockObject\MockObject $doctrineHelper */
        $doctrineHelper = $this->createMock(DoctrineHelper::class);
        $doctrineHelper
            ->expects($this->any())
            ->method('getEntityRepository')
            ->with(AuthorizeNetSettings::class)
            ->willReturn($this->authorizeNetSettingsRepository);

        $this->checker = new CIMRestriction($doctrineHelper);
    }

    public function testIsChannelActivationAllowedChannelHasNotApplicableType()
    {
        /** @var Channel $channel */
        $channel = $this->getEntity(Channel::class, ['id' => 1, 'type' => 'not_applicable_type']);

        $this->authorizeNetSettingsRepository
            ->expects($this->never())
            ->method('isChannelIntersectedByCIMEnabledWebsitesExist')
            ->with($channel);

        $this->assertTrue($this->checker->isChannelActivationAllowed($channel));
    }

    /**
     * @dataProvider isChannelActivationAllowedProvider
     *
     * @param bool $intersectedExists
     * @param bool $isAllowed
     */
    public function testIsChannelActivationAllowedChannelHasIsApplicableType($intersectedExists, $isAllowed)
    {
        /** @var Channel $channel */
        $channel = $this->getEntity(
            Channel::class,
            [
                'id' => 1,
                'type' => AuthorizeNetChannelType::TYPE
            ]
        );

        $this->authorizeNetSettingsRepository
            ->expects($this->once())
            ->method('isChannelIntersectedByCIMEnabledWebsitesExist')
            ->willReturn($intersectedExists);

        $this->assertEquals($isAllowed, $this->checker->isChannelActivationAllowed($channel));
    }

    /**
     * @return array
     */
    public function isChannelActivationAllowedProvider()
    {
        return [
            'Channel with active CIM functionality and intersected by enabled cim websites exists' => [
                'intersectedExists' => true,
                'isAllowed' => false
            ],
            'Channel with active CIM functionality and intersected by enabled cim websites not exists' => [
                'intersectedExists' => false,
                'isAllowed' => true
            ],
        ];
    }
}
