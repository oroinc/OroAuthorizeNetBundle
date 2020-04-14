<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit;

use Oro\Bundle\AuthorizeNetBundle\DependencyInjection\Compiler\RequestCompilerPass;
use Oro\Bundle\AuthorizeNetBundle\DependencyInjection\Compiler\RequestConfiguratorCompilerPass;
use Oro\Bundle\AuthorizeNetBundle\DependencyInjection\OroAuthorizeNetExtension;
use Oro\Bundle\AuthorizeNetBundle\OroAuthorizeNetBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OroAuthorizeNetBundleTest extends \PHPUnit\Framework\TestCase
{
    /** @var OroAuthorizeNetBundle */
    private $bundle;

    protected function setUp(): void
    {
        $this->bundle = new OroAuthorizeNetBundle();
    }

    public function testBuild()
    {
        /** @var ContainerBuilder|\PHPUnit\Framework\MockObject\MockObject $containerBuilder */
        $containerBuilder = $this->getMockBuilder(ContainerBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods(['addCompilerPass'])
            ->getMock();

        $expectations = [
            $this->isInstanceOf(RequestCompilerPass::class),
            $this->isInstanceOf(RequestConfiguratorCompilerPass::class)
        ];

        foreach ($expectations as $key => $expectation) {
            $containerBuilder->expects($this->at($key))
                ->method('addCompilerPass')
                ->with($expectation);
        }

        $this->bundle->build($containerBuilder);
    }

    public function testGetContainerExtension()
    {
        $this->assertInstanceOf(OroAuthorizeNetExtension::class, $this->bundle->getContainerExtension());
    }
}
