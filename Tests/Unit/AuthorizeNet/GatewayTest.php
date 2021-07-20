<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Client\ClientInterface;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Gateway;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option\OptionsResolver;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Request\RequestInterface;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Request\RequestRegistry;

class GatewayTest extends \PHPUnit\Framework\TestCase
{
    const CONFIGURABLE_OPTION = 'CONFIGURABLE_OPTION';

    /** @var ClientInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $client;

    /** @var RequestRegistry|\PHPUnit\Framework\MockObject\MockObject */
    protected $requestRegistry;

    /** @var Gateway */
    protected $gateway;

    protected function setUp(): void
    {
        $this->client = $this->createMock(ClientInterface::class);
        $this->requestRegistry = $this->createMock(RequestRegistry::class);
        $this->gateway = new Gateway($this->client, $this->requestRegistry);
    }

    /**
     * @dataProvider requestDataProvider
     */
    public function testRequest($testMode, $expectedHostAddress)
    {
        $request = $this->createMock(RequestInterface::class);
        $request
            ->expects($this->once())
            ->method('configureOptions')
            ->with(
                $this->isInstanceOf(OptionsResolver::class)
            )
            ->willReturnCallback(function (OptionsResolver $resolver) {
                $resolver->setDefined(self::CONFIGURABLE_OPTION);
            });

        $transactionType = 'TRANSACTION_TYPE';

        $options = [
            self::CONFIGURABLE_OPTION => 'value',
        ];

        $this->requestRegistry
            ->expects($this->once())
            ->method('getRequest')
            ->with($transactionType)
            ->willReturn($request);

        $this->gateway->setTestMode($testMode);
        $this->client
            ->expects($this->once())
            ->method('send')
            ->with($expectedHostAddress, $transactionType, $options);

        $this->gateway->request($transactionType, $options);
    }

    /**
     * @return array
     */
    public function requestDataProvider()
    {
        return [
            [false, Gateway::ADDRESS_PRODUCTION],
            [true, Gateway::ADDRESS_SANDBOX],
        ];
    }
}
