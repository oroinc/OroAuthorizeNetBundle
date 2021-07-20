<?php

namespace Oro\Bundle\AuthorizeNetBundle\Method\Option\Provider;

/**
 * Opaque options are specific options, which are transfered from Authorize.Net throw frontend
 * and transaction.additionalData
 */
interface OpaqueOptionProviderInterface
{
    public function getDataDescriptor(): string;

    public function getDataValue(): string;
}
