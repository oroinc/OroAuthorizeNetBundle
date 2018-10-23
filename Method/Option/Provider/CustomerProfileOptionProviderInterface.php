<?php

namespace Oro\Bundle\AuthorizeNetBundle\Method\Option\Provider;

/**
 * Represents component that is able to provide (find/create)
 * options related to CustomerProfile
 */
interface CustomerProfileOptionProviderInterface
{
    /** @return bool */
    public function isCustomerProfileExists(): bool;

    /** @return string */
    public function getExistingCustomerProfileId(): string;

    /** @return string */
    public function getExistingCustomerPaymentProfileId(): string;

    /** @return string */
    public function getGeneratedNewCustomerProfileId(): string;

    /** @return string */
    public function getEmail(): ?string;
}
