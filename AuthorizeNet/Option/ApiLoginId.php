<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

/**
 * Option class to represent merchantAuthentication::name field (Authorize.Net SDK)
 */
class ApiLoginId extends AbstractOption
{
    public const API_LOGIN_ID = 'api_login_id';

    #[\Override]
    protected function getName()
    {
        return self::API_LOGIN_ID;
    }

    #[\Override]
    protected function getAllowedTypes()
    {
        return 'string';
    }
}
