<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

/**
 * Option class to add options for defining charge type
 * having this option, it is possible to configure type of charge during "createTransactionRequest" REQUEST
 * by adding/removing specific options:
 *
 * in case TYPE_CREDIT_CARD, required options are:
 *  [DataDescriptor, DataValue]
 * in case TYPE_PAYMENT_PROFILE
 *  [CustomerProfileId, CustomerPaymentProfileId, CardCode]
 */
class ChargeType extends AbstractOption
{
    public const NAME = 'charge_type';

    public const TYPE_CREDIT_CARD = 1;
    public const TYPE_PAYMENT_PROFILE = 2;

    public const ALLOWED_VALUES = [self::TYPE_CREDIT_CARD, self::TYPE_PAYMENT_PROFILE];

    /** @return string */
    #[\Override]
    protected function getName()
    {
        return self::NAME;
    }

    #[\Override]
    protected function getAllowedTypes()
    {
        return 'int';
    }

    #[\Override]
    protected function getAllowedValues()
    {
        return self::ALLOWED_VALUES;
    }
}
