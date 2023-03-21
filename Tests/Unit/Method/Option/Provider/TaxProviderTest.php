<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\Method\Option\Provider;

use Oro\Bundle\AuthorizeNetBundle\Method\Option\Provider\TaxProvider;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;
use Oro\Bundle\TaxBundle\Model\Result;
use Oro\Bundle\TaxBundle\Provider\TaxProviderInterface;
use Oro\Bundle\TaxBundle\Provider\TaxProviderRegistry;
use Oro\Component\Testing\Unit\EntityTrait;

class TaxProviderTest extends \PHPUnit\Framework\TestCase
{
    use EntityTrait;

    /** @var DoctrineHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $doctrineHelper;

    /** @var TaxProviderRegistry|\PHPUnit\Framework\MockObject\MockObject */
    private $taxProviderRegistry;

    /** @var PaymentTransaction|\PHPUnit\Framework\MockObject\MockObject */
    private $paymentTransaction;

    /** @var TaxProvider */
    private $provider;

    protected function setUp(): void
    {
        $this->doctrineHelper = $this->createMock(DoctrineHelper::class);
        $this->taxProviderRegistry = $this->createMock(TaxProviderRegistry::class);

        $paymentTransaction = new PaymentTransaction();
        $paymentTransaction->setEntityClass(\stdClass::class);
        $paymentTransaction->setEntityIdentifier(1);

        $this->provider = new TaxProvider(
            $this->doctrineHelper,
            $this->taxProviderRegistry,
            $paymentTransaction
        );
    }

    public function testGetTaxAmount()
    {
        $expectedTaxAmount = 10;
        $taxProvider = $this->createMock(TaxProviderInterface::class);
        $taxProvider->expects($this->once())
            ->method('getTax')
            ->willReturn(Result::jsonDeserialize(['total' => ['taxAmount' => $expectedTaxAmount]]));

        $this->taxProviderRegistry->expects($this->once())
            ->method('getEnabledProvider')
            ->willReturn($taxProvider);

        $actualTaxAmount = $this->provider->getTaxAmount();
        $this->assertEquals($expectedTaxAmount, $actualTaxAmount);
        $this->assertIsFloat($actualTaxAmount);
    }
}
