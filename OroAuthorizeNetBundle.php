<?php

namespace Oro\Bundle\AuthorizeNetBundle;

use Oro\Bundle\AuthorizeNetBundle\DependencyInjection\OroAuthorizeNetExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * The AuthorizeNetBundle bundle class.
 */
class OroAuthorizeNetBundle extends Bundle
{
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
