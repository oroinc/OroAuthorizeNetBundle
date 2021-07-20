<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

/**
 * Interface for api request option
 */
interface OptionInterface
{
    public function configureOption(OptionsResolver $resolver);
}
