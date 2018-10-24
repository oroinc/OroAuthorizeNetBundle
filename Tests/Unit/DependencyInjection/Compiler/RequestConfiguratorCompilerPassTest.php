<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\DependencyInjection\Compiler;

use Oro\Bundle\AuthorizeNetBundle\DependencyInjection\Compiler\RequestConfiguratorCompilerPass;
use Oro\Component\DependencyInjection\Tests\Unit\AbstractExtensionCompilerPassTest;

class RequestConfiguratorCompilerPassTest extends AbstractExtensionCompilerPassTest
{
    public function testProcess()
    {
        $this->assertServiceDefinitionMethodCalled(RequestConfiguratorCompilerPass::METHOD_NAME);
        $this->assertContainerBuilderCalled();

        $this->getCompilerPass()->process($this->containerBuilder);
    }

    /**
     * {@inheritdoc}
     */
    protected function getCompilerPass()
    {
        return new RequestConfiguratorCompilerPass();
    }

    /**
     * {@inheritdoc}
     */
    protected function getServiceId()
    {
        return RequestConfiguratorCompilerPass::SERVICE_ID;
    }

    /**
     * {@inheritdoc}
     */
    protected function getTagName()
    {
        return RequestConfiguratorCompilerPass::TAG_NAME;
    }
}
