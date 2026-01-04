<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

/**
 * Option class to represent solutionId field (Authorize.Net SDK, CreateTransactionRequest)
 */
class SolutionId extends AbstractOption
{
    public const SOLUTION_ID = 'solution_id';

    #[\Override]
    protected function getName()
    {
        return self::SOLUTION_ID;
    }

    #[\Override]
    protected function getAllowedTypes()
    {
        return 'string';
    }
}
