<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Response;

/**
 * Interface for define active transaction
 */
interface ResponseActiveTransactionInterface
{
    /**
     * @return bool
     */
    public function isActive();
}
