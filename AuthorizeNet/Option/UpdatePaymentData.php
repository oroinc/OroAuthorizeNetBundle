<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

/**
 * Option class for updatePaymentData flag (Authorize.Net SDK)
 */
class UpdatePaymentData extends AbstractOption
{
    public const UPDATE_PAYMENT_DATA = 'update_payment_data';

    #[\Override]
    public function getName()
    {
        return self::UPDATE_PAYMENT_DATA;
    }

    #[\Override]
    public function getAllowedTypes()
    {
        return 'bool';
    }
}
