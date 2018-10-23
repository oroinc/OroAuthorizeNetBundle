<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Request;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option\OptionsResolver;

/**
 * Request interface
 */
interface RequestInterface
{
    /**
     * @return string
     */
    public function getType(): string;

    /**
     * @param OptionsResolver $optionsResolver
     */
    public function configureOptions(OptionsResolver $optionsResolver): void;
}
