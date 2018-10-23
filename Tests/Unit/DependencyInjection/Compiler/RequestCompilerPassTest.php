<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\DependencyInjection\Compiler;

use Oro\Bundle\AuthorizeNetBundle\DependencyInjection\Compiler\RequestCompilerPass;
use Oro\Component\DependencyInjection\Tests\Unit\AbstractExtensionCompilerPassTest;

class RequestCompilerPassTest extends AbstractExtensionCompilerPassTest
{
    public function testProcess()
    {
        $this->assertServiceDefinitionMethodCalled(RequestCompilerPass::METHOD_NAME);
        $this->assertContainerBuilderCalled();

        $this->getCompilerPass()->process($this->containerBuilder);
    }

    /**
     * {@inheritdoc}
     */
    protected function getCompilerPass()
    {
        return new RequestCompilerPass();
    }

    /**
     * {@inheritdoc}
     */
    protected function getServiceId()
    {
        return RequestCompilerPass::SERVICE_ID;
    }

    /**
     * {@inheritdoc}
     */
    protected function getTagName()
    {
        return RequestCompilerPass::TAG_NAME;
    }
}
