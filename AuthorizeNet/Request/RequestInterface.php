<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Request;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option\OptionsResolver;

/**
 * Request interface
 */
interface RequestInterface
{
    public function getType(): string;

    public function configureOptions(OptionsResolver $optionsResolver): void;
}
