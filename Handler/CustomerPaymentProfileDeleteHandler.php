<?php

namespace Oro\Bundle\AuthorizeNetBundle\Handler;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\AuthorizeNetBundle\Entity\CustomerPaymentProfile;
use Oro\Bundle\AuthorizeNetBundle\Helper\RequestSender;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

/**
 * Delete CustomerPaymentProfile entity (with api request)
 */
class CustomerPaymentProfileDeleteHandler
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

    public function handleDelete(CustomerPaymentProfile $paymentProfile)
    {
        $this->requestSender->deleteCustomerPaymentProfile($paymentProfile);
        /** @var EntityManager $manager */
        $manager = $this->doctrineHelper->getEntityManager($paymentProfile);
        $manager->remove($paymentProfile);
        $manager->flush();
    }
}
