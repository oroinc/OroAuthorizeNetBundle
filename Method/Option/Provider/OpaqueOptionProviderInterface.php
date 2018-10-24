<?php

namespace Oro\Bundle\AuthorizeNetBundle\Method\Option\Provider;

/**
 * Opaque options are specific options, which are transfered from Authorize.Net throw frontend
 * and transaction.additionalData
 */
interface OpaqueOptionProviderInterface
{
    /**
     * @return string
     */
    public function getDataDescriptor(): string;

    /**
     * @return string
     */
    public function getDataValue(): string;
}
