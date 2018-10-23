<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

/**
 * Interface for api request option
 */
interface OptionInterface
{
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOption(OptionsResolver $resolver);
}
