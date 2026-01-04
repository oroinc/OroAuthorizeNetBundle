<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

/**
 * Option class to represent customerPaymentProfile::type
 */
class ProfileType extends AbstractOption
{
    public const PROFILE_TYPE = 'profile_type';

    public const CREDITCARD_TYPE = 'creditcard';
    public const ECHECK_TYPE = 'echeck';

    #[\Override]
    protected function getName()
    {
        return self::PROFILE_TYPE;
    }

    #[\Override]
    protected function getAllowedValues()
    {
        return [self::CREDITCARD_TYPE, self::ECHECK_TYPE];
    }
}
