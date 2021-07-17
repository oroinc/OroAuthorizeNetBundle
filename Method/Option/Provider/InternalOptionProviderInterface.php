<?php

namespace Oro\Bundle\AuthorizeNetBundle\Method\Option\Provider;

/**
 * Internal options are those who are needed for control flow handle
 */
interface InternalOptionProviderInterface
{
    public function getProfileId(): ?int;

    public function getCardCode(): ?string;

    public function getCreateProfile(): ?bool;

    public function isCIMEnabled(): bool;
}
