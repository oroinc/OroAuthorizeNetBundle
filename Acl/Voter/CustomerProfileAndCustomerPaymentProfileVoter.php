<?php

namespace Oro\Bundle\AuthorizeNetBundle\Acl\Voter;

use Oro\Bundle\AuthorizeNetBundle\Entity\CustomerPaymentProfile;
use Oro\Bundle\AuthorizeNetBundle\Entity\CustomerProfile;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\SecurityBundle\Acl\Voter\EntityClassResolverUtil;
use Oro\Bundle\SecurityBundle\Acl\Voter\EntityIdentifierResolverUtil;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * Prevents access to non owned customer profile & payment profiles on the storefront.
 */
class CustomerProfileAndCustomerPaymentProfileVoter implements VoterInterface
{
    private DoctrineHelper $doctrineHelper;
    private string $className;

    public function __construct(DoctrineHelper $doctrineHelper, string $className)
    {
        $this->doctrineHelper = $doctrineHelper;
        $this->className = $className;
    }

    /**
     * {@inheritDoc}
     */
    public function vote(TokenInterface $token, $subject, array $attributes)
    {
        if (!\is_object($subject)) {
            return self::ACCESS_ABSTAIN;
        }

        $user = $token->getUser();
        if (null === $user) {
            return self::ACCESS_DENIED;
        }
        if (!$user instanceof CustomerUser) {
            return self::ACCESS_ABSTAIN;
        }

        $class = EntityClassResolverUtil::getEntityClass($subject);
        if ($class !== $this->className) {
            return self::ACCESS_ABSTAIN;
        }

        $identifier = EntityIdentifierResolverUtil::getEntityIdentifier($subject, $this->doctrineHelper);
        if (null === $identifier) {
            return self::ACCESS_ABSTAIN;
        }

        /** @var CustomerPaymentProfile|CustomerProfile|null $entity */
        $entity = $this->doctrineHelper->getEntityManagerForClass($class)->find($class, $identifier);
        if (null === $entity) {
            return self::ACCESS_ABSTAIN;
        }

        return $entity->getCustomerUser() === $user
            ? self::ACCESS_GRANTED
            : self::ACCESS_DENIED;
    }
}
