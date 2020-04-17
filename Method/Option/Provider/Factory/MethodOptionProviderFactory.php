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

    /**
     * MethodOptionProviderFactory constructor.
     * @param CustomerProfileProvider $customerProfileProvider
     * @param MerchantCustomerIdGenerator $merchantCustomerIdGenerator
     * @param DoctrineHelper $doctrineHelper
     * @param AddressExtractor $addressExtractor
     * @param TaxProviderRegistry $taxProviderRegistry
     * @param RequestStack $requestStack
     */
    public function __construct(
        CustomerProfileProvider $customerProfileProvider,
        MerchantCustomerIdGenerator $merchantCustomerIdGenerator,
        DoctrineHelper $doctrineHelper,
        AddressExtractor $addressExtractor,
        TaxProviderRegistry $taxProviderRegistry,
        RequestStack $requestStack
    ) {
        $this->customerProfileProvider = $customerProfileProvider;
        $this->merchantCustomerIdGenerator = $merchantCustomerIdGenerator;
        $this->doctrineHelper = $doctrineHelper;
        $this->addressExtractor = $addressExtractor;
        $this->taxProviderRegistry = $taxProviderRegistry;
        $this->requestStack = $requestStack;
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
        return new MethodOptionProvider(
            $config,
            $transaction,
            $this->customerProfileProvider,
            $this->doctrineHelper,
            $this->merchantCustomerIdGenerator,
            $this->requestStack
        );
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
