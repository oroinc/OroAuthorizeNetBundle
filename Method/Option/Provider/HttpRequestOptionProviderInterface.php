<?php

namespace Oro\Bundle\AuthorizeNetBundle\Method\Option\Provider;

/**
 * Represents component that is able to provide options based on HTTP Request object
 */
interface HttpRequestOptionProviderInterface
{
    /**
     * @return string|null
     */
    public function getClientIp(): ?string;
}
