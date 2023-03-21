<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Client\RequestConfigurator;

use net\authorize\api\contract\v1 as AnetAPI;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Client\RequestConfigurator\RequestConfiguratorInterface;

abstract class AbstractRequestConfiguratorTest extends \PHPUnit\Framework\TestCase
{
    protected RequestConfiguratorInterface $configurator;

    protected function setUp(): void
    {
        $this->configurator = $this->getConfigurator();
    }

    abstract protected function getConfigurator(): RequestConfiguratorInterface;

    /**
     * @dataProvider isApplicableProvider
     */
    public function testIsApplicable(AnetAPI\ANetApiRequestType $request, array $options, bool $expectedResult)
    {
        $this->assertEquals($expectedResult, $this->configurator->isApplicable($request, $options));
    }

    abstract public function isApplicableProvider(): array;

    /**
     * @dataProvider handleProvider
     */
    public function testHandle(
        AnetAPI\ANetApiRequestType $request,
        array $options,
        AnetAPI\ANetApiRequestType $expectedRequest
    ) {
        $customOptions = ['some_another_options' => 'value'];
        $options = array_merge($options, $customOptions);

        $this->configurator->handle($request, $options);

        // Configurator options removed, options that are not related to this configurator left
        $this->assertSame($customOptions, $options);
        $this->assertEquals($expectedRequest, $request);
    }

    abstract public function handleProvider(): array;
}
