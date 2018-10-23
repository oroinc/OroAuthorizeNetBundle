<?php

namespace Oro\Bundle\AuthorizeNetBundle\Method\Option\Provider;

/**
 * Payment options are actual payment values (amount, currency, also sourceTransaction for auth, charge, capture)
 */
interface PaymentOptionProviderInterface
{
    /**
     * @return float
     */
    public function getAmount(): float;

    /**
     * @return string
     */
    public function getCurrency(): string;

    /**
     * @return null|string
     */
    public function getOriginalTransaction(): ?string;
}
