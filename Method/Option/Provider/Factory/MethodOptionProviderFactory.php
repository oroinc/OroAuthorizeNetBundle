<?php

namespace Oro\Bundle\AuthorizeNetBundle\Method\Option\Provider\Factory;

use Oro\Bundle\AuthorizeNetBundle\Helper\MerchantCustomerIdGenerator;
use Oro\Bundle\AuthorizeNetBundle\Method\Config\AuthorizeNetConfigInterface;
use Oro\Bundle\AuthorizeNetBundle\Method\Option\Provider\AddressInfoProvider;
use Oro\Bundle\AuthorizeNetBundle\Method\Option\Provider\MethodOptionProvider;
use Oro\Bundle\AuthorizeNetBundle\Method\Option\Provider\MethodOptionProviderInterface;
use Oro\Bundle\AuthorizeNetBundle\Method\Option\Provider\TaxProvider;
use Oro\Bundle\AuthorizeNetBundle\Provider\CustomerProfileProvider;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;
use Oro\Bundle\PaymentBundle\Provider\AddressExtractor;
use Oro\Bundle\TaxBundle\Provider\TaxProviderRegistry;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Create Options Provider for Payment Method requests
 */
class MethodOptionProviderFactory
{
    /** @var CustomerProfileProvider */
    private $customerProfileProvider;

    /** @var MerchantCustomerIdGenerator */
    private $merchantCustomerIdGenerator;

    /** @var DoctrineHelper */
    private $doctrineHelper;

    /** @var AddressExtractor */
    private $addressExtractor;

    /** @var TaxProviderRegistry */
    private $taxProviderRegistry;

    /** @var RequestStack */
    private $requestStack;

    public function __construct(
        CustomerProfileProvider $customerProfileProvider,
        MerchantCustomerIdGenerator $merchantCustomerIdGenerator,
        DoctrineHelper $doctrineHelper,
        AddressExtractor $addressExtractor,
        TaxProviderRegistry $taxProviderRegistry
        // Will be added in version 4.2:
        // RequestStack $requestStack = null
    ) {
        $this->customerProfileProvider = $customerProfileProvider;
        $this->merchantCustomerIdGenerator = $merchantCustomerIdGenerator;
        $this->doctrineHelper = $doctrineHelper;
        $this->addressExtractor = $addressExtractor;
        $this->taxProviderRegistry = $taxProviderRegistry;
    }

    /**
     * @deprecated Will be removed in version 4.2, constructor injection will be used instead.
     */
    public function setRequestStack(RequestStack $requestStack): self
    {
        $this->requestStack = $requestStack;
        return $this;
    }

    /**
     * @param AuthorizeNetConfigInterface $config
     * @param PaymentTransaction $transaction
     * @return MethodOptionProviderInterface
     */
    public function createMethodOptionProvider(
        AuthorizeNetConfigInterface $config,
        PaymentTransaction $transaction
    ): MethodOptionProviderInterface {
        return (new MethodOptionProvider(
            $config,
            $transaction,
            $this->customerProfileProvider,
            $this->doctrineHelper,
            $this->merchantCustomerIdGenerator
        ))->setRequestStack($this->requestStack);
    }

    /**
     * @param PaymentTransaction $transaction
     * @return AddressInfoProvider
     */
    public function createAddressProvider(PaymentTransaction $transaction): AddressInfoProvider
    {
        return new AddressInfoProvider(
            $this->doctrineHelper,
            $this->addressExtractor,
            $transaction
        );
    }

    /**
     * @param PaymentTransaction $transaction
     * @return TaxProvider
     */
    public function createTaxProvider(PaymentTransaction $transaction): TaxProvider
    {
        return new TaxProvider(
            $this->doctrineHelper,
            $this->taxProviderRegistry,
            $transaction
        );
    }
}
