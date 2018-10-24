<?php

namespace Oro\Bundle\AuthorizeNetBundle\Method\Option\Provider;

/**
 * Merchant options are Authorize.Net integration options
 */
interface MerchantOptionProviderInterface
{
    /**
     * @return null|string
     */
    public function getSolutionId(): ?string;

    /**
     * @return string
     */
    public function getApiLoginId(): string;

    /**
     * @return string
     */
    public function getTransactionKey(): string;
}
