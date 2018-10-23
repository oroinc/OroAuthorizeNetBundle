<?php

namespace Oro\Bundle\AuthorizeNetBundle\Acl\Voter;

use Doctrine\ORM\EntityRepository;
use Oro\Bundle\AuthorizeNetBundle\Entity\CustomerPaymentProfile;
use Oro\Bundle\AuthorizeNetBundle\Entity\CustomerProfile;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\SecurityBundle\Acl\Voter\AbstractEntityVoter;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Security voter that prevents access to non owned customer profile & payment profiles on the front store
 */
class CustomerProfileAndCustomerPaymentProfileVoter extends AbstractEntityVoter
{
    /** @var TokenAccessorInterface */
    private $tokenAccessor;

    public function __construct(DoctrineHelper $doctrineHelper, TokenAccessorInterface $tokenAccessor)
    {
        parent::__construct($doctrineHelper);
        $this->tokenAccessor = $tokenAccessor;
    }

    /**
     * {@inheritdoc}
     */
    protected function supportsAttribute($attribute)
    {
        return true; //supports any attribute
    }

    /**
     * {@inheritdoc}
     */
    protected function supportsAttributes(array $attributes)
    {
        return true; //supports any attributes
    }

    /**
     * {@inheritDoc}
     */
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        if ($token->getUser() instanceof CustomerUser) {
            return parent::vote($token, $object, $attributes);
        }

        return self::ACCESS_ABSTAIN;
    }

    /**
     * {@inheritDoc}
     */
    protected function getPermissionForAttribute($class, $identifier, $attribute)
    {
        /** @var $repository EntityRepository */
        $repository = $this->doctrineHelper->getEntityRepository($class);

        /** @var CustomerPaymentProfile|CustomerProfile $object */
        $object = $repository->find($identifier);

        if (!$object) {
            return self::ACCESS_ABSTAIN;
        }

        $currentUser = $this->tokenAccessor->getUser();

        return $currentUser && $object->getCustomerUser() === $currentUser
            ? self::ACCESS_GRANTED
            : self::ACCESS_DENIED;
    }
}
