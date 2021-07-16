<?php

namespace Oro\Bundle\AuthorizeNetBundle\Method\Option\Provider;

/**
 * Merchant options are Authorize.Net integration options
 */
interface MerchantOptionProviderInterface
{
    public function getSolutionId(): ?string;

    public function getApiLoginId(): string;

    public function getTransactionKey(): string;
}
