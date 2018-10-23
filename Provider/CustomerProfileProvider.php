<?php

namespace Oro\Bundle\AuthorizeNetBundle\Provider;

use Oro\Bundle\AuthorizeNetBundle\Entity\CustomerProfile;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessor;

/**
 * Find profile profile by customerUser & itegration
 */
class CustomerProfileProvider
{
    /** @var  DoctrineHelper */
    protected $doctrineHelper;

    /** @var IntegrationProvider */
    protected $integrationProvider;

    /** @var TokenAccessor */
    protected $tokenAccessor;

    /**
     * @param DoctrineHelper $doctrineHelper
     * @param IntegrationProvider $integrationProvider
     * @param TokenAccessor $tokenAccessor
     */
    public function __construct(
        DoctrineHelper $doctrineHelper,
        IntegrationProvider $integrationProvider,
        TokenAccessor $tokenAccessor
    ) {
        $this->doctrineHelper = $doctrineHelper;
        $this->integrationProvider = $integrationProvider;
        $this->tokenAccessor = $tokenAccessor;
    }

    /**
     * @param CustomerUser|null $customerUser
     * @return CustomerProfile|null
     */
    public function findCustomerProfile(CustomerUser $customerUser = null)
    {
        if (null === $customerUser) {
            $customerUser = $this->tokenAccessor->getUser();
        }

        $customerProfileRepository = $this->doctrineHelper->getEntityRepository(CustomerProfile::class);

        $integration = $this->integrationProvider->getIntegration();
        /** @var CustomerProfile $customerProfile */
        $customerProfile = $customerProfileRepository->findOneBy([
            'customerUser' => $customerUser,
            'integration' => $integration
        ]);

        return $customerProfile;
    }
}
