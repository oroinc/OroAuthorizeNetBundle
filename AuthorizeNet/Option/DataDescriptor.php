<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

/**
 * Option class to represent dataDescriptor field (Authorize.Net SDK)
 */
class DataDescriptor extends AbstractOption
{
    const DATA_DESCRIPTOR = 'data_descriptor';

    #[\Override]
    protected function getName()
    {
        return self::DATA_DESCRIPTOR;
    }

    #[\Override]
    protected function getAllowedTypes()
    {
        return 'string';
    }
}
