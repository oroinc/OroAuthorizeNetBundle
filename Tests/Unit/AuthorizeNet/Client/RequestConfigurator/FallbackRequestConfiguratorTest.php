<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Client\RequestConfigurator;

use net\authorize\api\contract\v1 as AnetAPI;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Client\RequestConfigurator\FallbackRequestConfigurator;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class FallbackRequestConfiguratorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PropertyAccessor
     */
    protected $propertyAccessor;

    /**
     * @var FallbackRequestConfigurator
     */
    protected $fallbackRequestConfigurator;

    protected function setUp(): void
    {
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
        $this->fallbackRequestConfigurator = new FallbackRequestConfigurator($this->propertyAccessor);
    }

    protected function tearDown(): void
    {
        unset($this->fallbackRequestConfigurator, $this->propertyAccessor);
    }

    public function testIsApplicable()
    {
        $request = new AnetAPI\CreateTransactionRequest();
        $this->assertTrue($this->fallbackRequestConfigurator->isApplicable($request, []));
    }

    public function testHandle()
    {
        $request = new AnetAPI\CreateTransactionRequest();

        $transactionRequestType = $this->createMock(AnetAPI\TransactionRequestType::class);
        $clientId = 'client_id';

        $options = ['transactionRequest' => $transactionRequestType, 'clientId' => $clientId];

        $this->fallbackRequestConfigurator->handle($request, $options);

        $this->assertEquals($request->getTransactionRequest(), $transactionRequestType);
        $this->assertEquals($request->getClientId(), $clientId);
    }
}
