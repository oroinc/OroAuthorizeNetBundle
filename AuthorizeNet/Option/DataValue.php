<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

/**
 * Option class to represent dataValue field (Authorize.Net SDK)
 */
class DataValue extends AbstractOption
{
    public const DATA_VALUE = 'data_value';

    #[\Override]
    protected function getName()
    {
        return self::DATA_VALUE;
    }

    #[\Override]
    protected function getAllowedTypes()
    {
        return 'string';
    }
}
