<?php

namespace Oro\Bundle\AuthorizeNetBundle\DependencyInjection\Compiler;

use Oro\Component\DependencyInjection\Compiler\TaggedServicesCompilerPassTrait;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Compiler pass to collect all requests to registry
 */
class RequestCompilerPass implements CompilerPassInterface
{
    use TaggedServicesCompilerPassTrait;

    const TAG_NAME = 'oro_authorize_net.authorize_net.request';
    const SERVICE_ID = 'oro_authorize_net.authorize_net.request.registry';
    const METHOD_NAME = 'addRequest';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $this->registerTaggedServices(
            $container,
            self::SERVICE_ID,
            self::TAG_NAME,
            self::METHOD_NAME
        );
    }
}
