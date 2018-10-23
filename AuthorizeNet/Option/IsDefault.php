<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

/**
 * Option class to represent defaultPaymentProfile field (Authorize.Net SDK, Customer Profile)
 */
class IsDefault extends AbstractOption
{
    const IS_DEFAULT = 'is_default';

    /**
     * {@inheritdoc}
     */
    protected function getName()
    {
        return self::IS_DEFAULT;
    }

    /**
     * {@inheritdoc}
     */
    protected function getAllowedTypes()
    {
        return 'bool';
    }
}
