<?php

namespace Oro\Bundle\AuthorizeNetBundle;

use Oro\Bundle\AuthorizeNetBundle\DependencyInjection\Compiler\RequestCompilerPass;
use Oro\Bundle\AuthorizeNetBundle\DependencyInjection\Compiler\RequestConfiguratorCompilerPass;
use Oro\Bundle\AuthorizeNetBundle\DependencyInjection\OroAuthorizeNetExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * AuthorizeNet bundle class
 */
class OroAuthorizeNetBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new RequestCompilerPass());
        $container->addCompilerPass(new RequestConfiguratorCompilerPass());
    }

    /**
     * {@inheritdoc}
     */
    public function getContainerExtension()
    {
        if (!$this->extension) {
            $this->extension = new OroAuthorizeNetExtension();
        }

        return $this->extension;
    }
}
