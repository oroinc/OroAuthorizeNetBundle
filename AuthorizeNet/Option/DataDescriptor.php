<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

/**
 * Option class to represent dataDescriptor field (Authorize.Net SDK)
 */
class DataDescriptor extends AbstractOption
{
    const DATA_DESCRIPTOR = 'data_descriptor';

    /**
     * {@inheritdoc}
     */
    protected function getName()
    {
        return self::DATA_DESCRIPTOR;
    }

    /**
     * {@inheritdoc}
     */
    protected function getAllowedTypes()
    {
        return 'string';
    }
}
