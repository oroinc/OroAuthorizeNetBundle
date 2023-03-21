<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Client\RequestConfigurator;

use net\authorize\api\contract\v1\CreateTransactionRequest;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Client\RequestConfigurator\MerchantAuthenticationRequestConfigurator;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

class MerchantAuthenticationRequestConfiguratorTest extends \PHPUnit\Framework\TestCase
{
    private MerchantAuthenticationRequestConfigurator $merchantAuthenticationRequestConfigurator;

    protected function setUp(): void
    {
        $this->merchantAuthenticationRequestConfigurator = new MerchantAuthenticationRequestConfigurator();
    }

    public function testIsApplicable()
    {
        $request = new CreateTransactionRequest();
        $this->assertFalse($this->merchantAuthenticationRequestConfigurator->isApplicable($request, []));

        $options = [
            Option\ApiLoginId::API_LOGIN_ID => 'api_login_id',
            Option\TransactionKey::TRANSACTION_KEY => 'transactionKey',
        ];

        $this->assertTrue($this->merchantAuthenticationRequestConfigurator->isApplicable($request, $options));
    }

    public function testHandle()
    {
        $request = new CreateTransactionRequest();

        $anotherOptions = ['someOption' => 'someValue'];

        $configuratorOptions = [
            Option\ApiLoginId::API_LOGIN_ID => 'api_login_id',
            Option\TransactionKey::TRANSACTION_KEY => 'transactionKey',
        ];

        $options = array_merge($anotherOptions, $configuratorOptions);

        $this->merchantAuthenticationRequestConfigurator->handle($request, $options);

        // Configurator options removed, options that are not related to this configurator left
        $this->assertSame($anotherOptions, $options);

        $merchantAuthentication = $request->getMerchantAuthentication();
        $this->assertNotNull($merchantAuthentication);
        $this->assertEquals($configuratorOptions[Option\ApiLoginId::API_LOGIN_ID], $merchantAuthentication->getName());
        $this->assertEquals(
            $configuratorOptions[Option\TransactionKey::TRANSACTION_KEY],
            $merchantAuthentication->getTransactionKey()
        );
    }
}
