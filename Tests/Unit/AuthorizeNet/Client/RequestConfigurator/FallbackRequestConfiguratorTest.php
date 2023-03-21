<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Client\RequestConfigurator;

use net\authorize\api\contract\v1 as AnetAPI;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Client\RequestConfigurator\FallbackRequestConfigurator;
use Oro\Bundle\EntityExtendBundle\PropertyAccess;

class FallbackRequestConfiguratorTest extends \PHPUnit\Framework\TestCase
{
    private FallbackRequestConfigurator $fallbackRequestConfigurator;

    protected function setUp(): void
    {
        $this->fallbackRequestConfigurator = new FallbackRequestConfigurator(
            PropertyAccess::createPropertyAccessor()
        );
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
