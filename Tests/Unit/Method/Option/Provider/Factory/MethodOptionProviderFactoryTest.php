<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\Method\Option\Provider\Factory;

use Oro\Bundle\AuthorizeNetBundle\Helper\MerchantCustomerIdGenerator;
use Oro\Bundle\AuthorizeNetBundle\Method\Config\AuthorizeNetConfigInterface;
use Oro\Bundle\AuthorizeNetBundle\Method\Option\Provider\AddressInfoProvider;
use Oro\Bundle\AuthorizeNetBundle\Method\Option\Provider\Factory\MethodOptionProviderFactory;
use Oro\Bundle\AuthorizeNetBundle\Method\Option\Provider\MethodOptionProviderInterface;
use Oro\Bundle\AuthorizeNetBundle\Method\Option\Provider\TaxProvider;
use Oro\Bundle\AuthorizeNetBundle\Provider\CustomerProfileProvider;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;
use Oro\Bundle\PaymentBundle\Provider\AddressExtractor;
use Oro\Bundle\TaxBundle\Provider\TaxProviderRegistry;

class MethodOptionProviderFactoryTest extends \PHPUnit\Framework\TestCase
{
    /** @var CustomerProfileProvider */
    private $customerProfileProvider;

    /** @var MerchantCustomerIdGenerator */
    private $merchantCustomerIdGenerator;

    /** @var DoctrineHelper */
    private $doctrineHelper;

    /** @var AddressExtractor */
    protected $addressExtractor;

    /** @var TaxProviderRegistry */
    private $taxProviderRegistry;

    /** @var MethodOptionProviderFactory*/
    private $factory;

    /** @var PaymentTransaction */
    private $transaction;

    public function setUp()
    {
        $this->customerProfileProvider = $this->createMock(CustomerProfileProvider::class);
        $this->merchantCustomerIdGenerator = $this->createMock(MerchantCustomerIdGenerator::class);
        $this->doctrineHelper = $this->createMock(DoctrineHelper::class);
        $this->addressExtractor = $this->createMock(AddressExtractor::class);
        $this->taxProviderRegistry = $this->createMock(TaxProviderRegistry::class);
        $this->transaction = $this->createMock(PaymentTransaction::class);

        $this->factory = new MethodOptionProviderFactory(
            $this->customerProfileProvider,
            $this->merchantCustomerIdGenerator,
            $this->doctrineHelper,
            $this->addressExtractor,
            $this->taxProviderRegistry
        );
    }

    public function testCreateMethodOptionProvider()
    {
        $config = $this->createMock(AuthorizeNetConfigInterface::class);
        $methodOptionsProvider = $this->factory->createMethodOptionProvider($config, $this->transaction);

        $this->assertInstanceOf(MethodOptionProviderInterface::class, $methodOptionsProvider);
    }

    public function testCreateAddressProvider()
    {
        $addressProvider = $this->factory->createAddressProvider($this->transaction);

        $this->assertInstanceOf(AddressInfoProvider::class, $addressProvider);
    }

    public function testCreateTaxProvider()
    {
        $taxProvider = $this->factory->createTaxProvider($this->transaction);

        $this->assertInstanceOf(TaxProvider::class, $taxProvider);
    }
}
