<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Client\RequestConfigurator;

use net\authorize\api\contract\v1 as AnetAPI;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Client\RequestConfigurator\RequestConfiguratorInterface;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

abstract class AbstractRequestConfiguratorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var RequestConfiguratorInterface
     */
    protected $configurator;

    protected function setUp()
    {
        $this->configurator = $this->getConfigurator();
    }

    /**
     * @return RequestConfiguratorInterface
     */
    abstract protected function getConfigurator();

    /**
     * @return int
     */
    abstract protected function getPriority();

    public function testGetPriority()
    {
        $this->assertEquals($this->getPriority(), $this->configurator->getPriority());
    }

    /**
     * @dataProvider isApplicableProvider
     * @param AnetAPI\ANetApiRequestType $request
     * @param array $options
     * @param bool $expectedResult
     */
    public function testIsApplicable(AnetAPI\ANetApiRequestType $request, array $options, bool $expectedResult)
    {
        $this->assertEquals($expectedResult, $this->configurator->isApplicable($request, $options));
    }

    /**
     * @return array
     */
    abstract public function isApplicableProvider();

    /**
     * @dataProvider handleProvider
     * @param AnetAPI\ANetApiRequestType $request
     * @param array $options
     * @param AnetAPI\ANetApiRequestType $expectedRequest
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

    /**
     * @return array
     */
    abstract public function handleProvider();
}
