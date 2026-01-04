<?php

namespace Oro\Bundle\AuthorizeNetBundle\Validator\Constraints;

use Oro\Bundle\AuthorizeNetBundle\Entity\AuthorizeNetSettings;
use Oro\Bundle\AuthorizeNetBundle\Entity\Repository\AuthorizeNetSettingsRepository;
use Oro\Bundle\AuthorizeNetBundle\Form\Extension\EnabledCIMWebsitesSelectExtension;
use Oro\Bundle\AuthorizeNetBundle\Integration\AuthorizeNetChannelType;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Provider\WebsiteProviderInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Check if there is only one integration with enabled CIM for the website
 */
class ForbidToReuseEnabledCIMWebsitesValidator extends ConstraintValidator
{
    public const ALIAS = 'oro_authorize_net.validator.forbid_to_reuse_enabled_cim_websites';

    /** @var DoctrineHelper */
    private $doctrineHelper;

    /** @var WebsiteProviderInterface */
    private $websiteProvider;

    public function __construct(DoctrineHelper $doctrineHelper, WebsiteProviderInterface $websiteProvider)
    {
        $this->doctrineHelper = $doctrineHelper;
        $this->websiteProvider = $websiteProvider;
    }

    /**
     * @param AuthorizeNetSettings            $entity
     * @param ForbidToReuseEnabledCIMWebsites $constraint
     */
    #[\Override]
    public function validate($entity, Constraint $constraint)
    {
        if (! $entity instanceof AuthorizeNetSettings) {
            return;
        }

        if ($entity->getChannel() && !$entity->getChannel()->isEnabled()) {
            return;
        }

        if (!$entity->isEnabledCIM() || 0 === count($entity->getEnabledCIMWebsites())) {
            return;
        }

        /** @var AuthorizeNetSettingsRepository $repository */
        $repository = $this->doctrineHelper->getEntityRepository(
            AuthorizeNetSettings::class
        );

        $enabledSettings = $repository->getEnabledSettingsWithCIMByTypeAndWebsites(
            AuthorizeNetChannelType::TYPE,
            $entity->getEnabledCIMWebsites()->toArray(),
            $entity->getId()
        );

        if (empty($enabledSettings)) {
            return;
        }

        /** @var ExecutionContextInterface $context */
        $context = $this->context;
        if (1 === count($this->websiteProvider->getWebsiteIds())) {
            $context
                ->buildViolation($constraint->messageSingleWebsite)
                ->atPath(EnabledCIMWebsitesSelectExtension::FIELD_NAME)
                ->addViolation();
        } else {
            $reusedWebsiteNames = $this->getReusedWebsiteNames($entity, $enabledSettings);
            $websiteMessageParameter = '"'. join('", "', $reusedWebsiteNames) .'"';

            $context
                ->buildViolation($constraint->messageMultiWebsite)
                ->setParameter('{{ websites }}', $websiteMessageParameter)
                ->atPath(EnabledCIMWebsitesSelectExtension::FIELD_NAME)
                ->addViolation();
        }
    }

    /**
     * @param AuthorizeNetSettings   $entity
     * @param AuthorizeNetSettings[] $enabledSettings
     *
     * @return array
     */
    private function getReusedWebsiteNames(AuthorizeNetSettings $entity, array $enabledSettings)
    {
        $selectedWebsiteNames = $this->getWebsiteNamesFromSettings($entity);

        $reusedWebsiteNames = [];
        foreach ($enabledSettings as $enabledSetting) {
            $reusedWebsiteNames = array_merge(
                $reusedWebsiteNames,
                array_intersect(
                    $selectedWebsiteNames,
                    $this->getWebsiteNamesFromSettings($enabledSetting)
                )
            );
        }

        return $reusedWebsiteNames;
    }

    /**
     * @param AuthorizeNetSettings $entity
     *
     * @return array
     */
    private function getWebsiteNamesFromSettings(AuthorizeNetSettings $entity)
    {
        return array_map(
            function (Website $website) {
                return $website->getName();
            },
            $entity->getEnabledCIMWebsites()->toArray()
        );
    }
}
