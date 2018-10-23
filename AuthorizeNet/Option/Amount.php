<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

/**
 * Option class to represent amount field (Authorize.Net SDK, CreateTransactionRequest)
 */
class Amount extends AbstractOption
{
    const AMOUNT = 'amount';

    /**
     * {@inheritdoc}
     */
    protected function getName()
    {
        return self::AMOUNT;
    }

    /**
     * {@inheritdoc}
     */
    protected function getAllowedTypes()
    {
        return ['float', 'integer'];
    }
}
