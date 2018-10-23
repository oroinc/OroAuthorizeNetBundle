<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

/**
 * Option class to represent customerPaymentProfile::type
 */
class ProfileType extends AbstractOption
{
    const PROFILE_TYPE = 'profile_type';

    const CREDITCARD_TYPE = 'creditcard';
    const ECHECK_TYPE = 'echeck';

    /**
     * {@inheritdoc}
     */
    protected function getName()
    {
        return self::PROFILE_TYPE;
    }

    /**
     * {@inheritdoc}
     */
    protected function getAllowedValues()
    {
        return [self::CREDITCARD_TYPE, self::ECHECK_TYPE];
    }
}
