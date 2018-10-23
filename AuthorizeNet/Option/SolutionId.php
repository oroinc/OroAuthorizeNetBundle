<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

/**
 * Option class to represent solutionId field (Authorize.Net SDK, CreateTransactionRequest)
 */
class SolutionId extends AbstractOption
{
    const SOLUTION_ID = 'solution_id';

    /**
     * {@inheritdoc}
     */
    protected function getName()
    {
        return self::SOLUTION_ID;
    }

    /**
     * {@inheritdoc}
     */
    protected function getAllowedTypes()
    {
        return 'string';
    }
}
