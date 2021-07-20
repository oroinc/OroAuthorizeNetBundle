<?php

namespace Oro\Bundle\AuthorizeNetBundle\Handler;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\AuthorizeNetBundle\Entity\CustomerProfile;
use Oro\Bundle\AuthorizeNetBundle\Helper\RequestSender;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

/**
 * Delete CustomerProfile entity (with api request)
 */
class CustomerProfileDeleteHandler
{
    /** @var DoctrineHelper */
    private $doctrineHelper;

    /** @var RequestSender */
    private $requestSender;

    public function __construct(DoctrineHelper $doctrineHelper, RequestSender $requestSender)
    {
        $this->doctrineHelper = $doctrineHelper;
        $this->requestSender = $requestSender;
    }

    public function handleDelete(CustomerProfile $customerProfile)
    {
        $this->requestSender->deleteCustomerProfile($customerProfile);
        /** @var EntityManager $manager */
        $manager = $this->doctrineHelper->getEntityManager($customerProfile);
        $manager->remove($customerProfile);
        $manager->flush();
    }
}
