<?php

namespace Oro\Bundle\AuthorizeNetBundle\Method\Config;

/**
 * Represents Authorize.Net advanced integration parameters as DTO Interface
 */
interface TransactionHoldConfigInterface
{
    /**
     * @return bool
     */
    public function isAllowHoldTransaction(): bool;
}
