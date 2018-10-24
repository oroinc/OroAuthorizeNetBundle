<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

/**
 * Option class to represent dataValue field (Authorize.Net SDK)
 */
class DataValue extends AbstractOption
{
    const DATA_VALUE = 'data_value';

    /**
     * {@inheritdoc}
     */
    protected function getName()
    {
        return self::DATA_VALUE;
    }

    /**
     * {@inheritdoc}
     */
    protected function getAllowedTypes()
    {
        return 'string';
    }
}
