<?php

namespace Oro\Bundle\AuthorizeNetBundle\Entity\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Oro\Bundle\AuthorizeNetBundle\Entity\AuthorizeNetSettings;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Oro\Bundle\WebsiteBundle\Entity\Website;

/**
 * Repository for AuthorizeNetSettings entity
 */
class AuthorizeNetSettingsRepository extends ServiceEntityRepository
{
    private ?AclHelper $aclHelper = null;

    public function setAclHelper(AclHelper $aclHelper): self
    {
        $this->aclHelper = $aclHelper;

        return $this;
    }

    /**
     * @param string $type
     * @return AuthorizeNetSettings[]
     */
    public function getEnabledSettingsByType($type)
    {
        $qb = $this->createQueryBuilder('settings')
            ->innerJoin('settings.channel', 'channel')
            ->andWhere('channel.enabled = true')
            ->andWhere('channel.type = :type')
            ->setParameter('type', $type);

        return $this->aclHelper?->apply($qb)->getResult();
    }

    /**
     * @param string   $type
     * @param array    $websites
     * @param int|null $excludedSettingId
     *
     * @return AuthorizeNetSettings[]
     */
    public function getEnabledSettingsWithCIMByTypeAndWebsites(
        string $type,
        array $websites,
        int $excludedSettingId = null
    ) {
        $qb =  $this->createQueryBuilder('settings');

        if (null !== $excludedSettingId) {
            $qb
                ->andWhere($qb->expr()->neq('settings.id', ':excludedSettingId'))
                ->setParameter('excludedSettingId', $excludedSettingId);
        }

        $qb
            ->innerJoin('settings.channel', 'channel')
            ->innerJoin('settings.enabledCIMWebsites', 'websites')
            ->andWhere('settings.enabledCIM = true')
            ->andWhere('channel.enabled = true')
            ->andWhere('channel.type = :type')
            ->andWhere($qb->expr()->in('websites', ':websites'))
            ->setParameter('type', $type)
            ->setParameter('websites', $websites)
            ->groupBy('settings.id');

        return $this->aclHelper?->apply($qb)->getResult();
    }

    /**
     * @param string $type
     * @param Website $website
     *
     * @return AuthorizeNetSettings[]
     */
    public function findCIMEnabledSettingsByTypeAndWebsite(string $type, Website $website)
    {
        $qb =  $this->createQueryBuilder('settings');
        $qb
            ->innerJoin('settings.channel', 'channel')
            ->innerJoin('settings.enabledCIMWebsites', 'websites')
            ->andWhere('settings.enabledCIM = true')
            ->andWhere('channel.enabled = true')
            ->andWhere('channel.type = :type')
            ->andWhere($qb->expr()->in('websites', ':websites'))
            ->setParameter('type', $type)
            ->setParameter('websites', [ $website ]);

        return $this->aclHelper?->apply($qb)->getResult();
    }

    /**
     * @param Channel $channel
     *
     * @return bool
     */
    public function isChannelIntersectedByCIMEnabledWebsitesExist(Channel $channel)
    {
        $subqueryBuilder = $this->createQueryBuilder('settingsInt');
        $subqueryBuilder
            ->select('websitesInt.id')
            ->innerJoin('settingsInt.channel', 'channelInt')
            ->innerJoin('settingsInt.enabledCIMWebsites', 'websitesInt')
            ->andWhere('settingsInt.enabledCIM = true')
            ->andWhere(
                $subqueryBuilder->expr()->eq(
                    'channelInt',
                    ':channelInt'
                )
            );

        $qb = $this->createQueryBuilder('settings');
        $qb
            ->select('1')
            ->setMaxResults(1)
            ->innerJoin('settings.channel', 'channel')
            ->innerJoin('settings.enabledCIMWebsites', 'websites')
            ->andWhere('settings.enabledCIM = true')
            ->andWhere('channel.enabled = true')
            ->andWhere(
                $subqueryBuilder->expr()->neq(
                    'channel',
                    ':channel'
                )
            )
            ->andWhere(
                $qb->expr()->in('websites.id', $subqueryBuilder->getDQL())
            )
            ->setParameter('channel', $channel)
            ->setParameter('channelInt', $channel);

        $result = $this->aclHelper?->apply($qb)->getScalarResult();

        return !empty($result);
    }
}
