<?php

namespace Oro\Bundle\AuthorizeNetBundle\Method\Option\Provider;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;
use Oro\Bundle\TaxBundle\Provider\TaxProviderRegistry;

/**
 * Tax information provider
 * get tax info by object
 */
class TaxProvider
{
    /** @var DoctrineHelper */
    private $doctrineHelper;

    /** @var TaxProviderRegistry */
    protected $taxProviderRegistry;

    /** @var PaymentTransaction */
    protected $paymentTransaction;

    /**
     * @param DoctrineHelper $doctrineHelper
     * @param TaxProviderRegistry $taxProviderRegistry
     * @param PaymentTransaction $paymentTransaction
     */
    public function __construct(
        DoctrineHelper $doctrineHelper,
        TaxProviderRegistry $taxProviderRegistry,
        $paymentTransaction
    ) {
        $this->doctrineHelper = $doctrineHelper;
        $this->taxProviderRegistry = $taxProviderRegistry;
        $this->paymentTransaction = $paymentTransaction;
    }

    /**
     * @return float
     */
    public function getTaxAmount()
    {
        $entity = $this->getEntity();
        $provider = $this->taxProviderRegistry->getEnabledProvider();
        $total = $provider->getTax($entity)->getTotal();

        return (float) $total->getTaxAmount();
    }

    /**
     * @return object
     */
    private function getEntity()
    {
        return $this->doctrineHelper->getEntityReference(
            $this->paymentTransaction->getEntityClass(),
            $this->paymentTransaction->getEntityIdentifier()
        );
    }
}
