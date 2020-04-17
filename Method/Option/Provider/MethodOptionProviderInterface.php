<?php

namespace Oro\Bundle\AuthorizeNetBundle\Method\Option\Provider;

/**
 * Aggregates Different OptionProviderInterfaces to be able injecting single provider which
 * can fetch all of those
 */
interface MethodOptionProviderInterface extends
    MerchantOptionProviderInterface,
    OpaqueOptionProviderInterface,
    CustomerProfileOptionProviderInterface,
    InternalOptionProviderInterface,
    PaymentOptionProviderInterface,
    HttpRequestOptionProviderInterface
{
}
