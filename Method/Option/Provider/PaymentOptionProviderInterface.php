<?php

namespace Oro\Bundle\AuthorizeNetBundle\Method\Option\Provider;

/**
 * Payment options are actual payment values (amount, currency, also sourceTransaction for auth, charge, capture)
 */
interface PaymentOptionProviderInterface
{
    public function getAmount(): float;

    public function getCurrency(): string;

    public function getOriginalTransaction(): ?string;
}
