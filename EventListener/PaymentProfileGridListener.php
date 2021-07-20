<?php

namespace Oro\Bundle\AuthorizeNetBundle\EventListener;

use Oro\Bundle\AuthorizeNetBundle\Provider\CustomerProfileProvider;
use Oro\Bundle\AuthorizeNetBundle\Provider\PaymentProfileProvider;
use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;

/**
 * Grid listener to add where condition to restrict access only to actual payment profiles
 */
class PaymentProfileGridListener
{
    /**
     * @var PaymentProfileProvider
     */
    protected $paymentProfileProvider;

    /**
     * @var CustomerProfileProvider
     */
    protected $customerProfileProvider;

    public function __construct(
        PaymentProfileProvider $paymentProfileProvider,
        CustomerProfileProvider $customerProfileProvider
    ) {
        $this->paymentProfileProvider = $paymentProfileProvider;
        $this->customerProfileProvider = $customerProfileProvider;
    }

    public function onBuildAfter(BuildAfter $event)
    {
        $datasource = $event->getDatagrid()->getDatasource();
        if (!$datasource instanceof OrmDatasource) {
            return;
        }

        $customerProfile = $this->customerProfileProvider->findCustomerProfile();
        $externalIds = $this->paymentProfileProvider->getPaymentProfileExternalIds($customerProfile);

        $queryBuilder = $datasource->getQueryBuilder();
        $queryBuilder->andWhere($queryBuilder->expr()->in('profile.customerPaymentProfileId', ':externalIds'));
        $queryBuilder->setParameter('externalIds', $externalIds);
    }
}
