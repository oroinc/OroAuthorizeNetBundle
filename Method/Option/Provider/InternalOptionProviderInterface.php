<?php

namespace Oro\Bundle\AuthorizeNetBundle\Method\Option\Provider;

/**
 * Internal options are those who are needed for control flow handle
 */
interface InternalOptionProviderInterface
{
    /**
     * @return int|null
     */
    public function getProfileId(): ?int;

    /**
     * @return null|string
     */
    public function getCardCode(): ?string;

    /**
     * @return null|bool
     */
    public function getCreateProfile(): ?bool;

    /**
     * @return bool
     */
    public function isCIMEnabled(): bool;
}
