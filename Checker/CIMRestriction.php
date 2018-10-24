<?php

namespace Oro\Bundle\AuthorizeNetBundle\Checker;

use Oro\Bundle\AuthorizeNetBundle\Entity\AuthorizeNetSettings;
use Oro\Bundle\AuthorizeNetBundle\Entity\Repository\AuthorizeNetSettingsRepository;
use Oro\Bundle\AuthorizeNetBundle\Integration\AuthorizeNetChannelType;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\IntegrationBundle\Entity\Channel;

/**
 * Class to performs check for auth.net integration activation.
 * In case there are other integration with CIM setting enabled for the used websites,
 * it should not be possible to activate such integration.
 */
class CIMRestriction
{
    /** @var DoctrineHelper */
    private $doctrineHelper;

    /**
     * @param DoctrineHelper $doctrineHelper
     */
    public function __construct(DoctrineHelper $doctrineHelper)
    {
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * @param Channel $channel
     *
     * @return bool
     */
    public function isChannelActivationAllowed(Channel $channel)
    {
        if (AuthorizeNetChannelType::TYPE !== $channel->getType()) {
            return true;
        }

        /** @var AuthorizeNetSettingsRepository $repository */
        $repository = $this->doctrineHelper->getEntityRepository(AuthorizeNetSettings::class);

        return !$repository->isChannelIntersectedByCIMEnabledWebsitesExist($channel);
    }
}
