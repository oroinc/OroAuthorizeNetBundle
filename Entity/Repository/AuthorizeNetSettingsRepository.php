<?php

namespace Oro\Bundle\AuthorizeNetBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Oro\Bundle\AuthorizeNetBundle\Entity\AuthorizeNetSettings;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\WebsiteBundle\Entity\Website;

/**
 * Repository for AuthorizeNetSettings entity
 */
class AuthorizeNetSettingsRepository extends EntityRepository
{
    /**
     * @param string $type
     * @return AuthorizeNetSettings[]
     */
    public function getEnabledSettingsByType($type)
    {
        return $this->createQueryBuilder('settings')
            ->innerJoin('settings.channel', 'channel')
            ->andWhere('channel.enabled = true')
            ->andWhere('channel.type = :type')
            ->setParameter('type', $type)
            ->getQuery()
            ->getResult();
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

        return $qb->getQuery()->getResult();
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

        return $qb->getQuery()->getResult();
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

        $result = $qb->getQuery()->getScalarResult();

        return !empty($result);
    }
}
