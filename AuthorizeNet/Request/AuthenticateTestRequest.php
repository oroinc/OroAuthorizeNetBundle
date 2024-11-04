<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Request;

/**
 * Class to represent authenticateTestRequest (Authorize.Net API)
 */
class AuthenticateTestRequest extends AbstractRequest
{
    public const REQUEST_TYPE = 'authenticateTestRequest';

    #[\Override]
    public function getType(): string
    {
        return self::REQUEST_TYPE;
    }
}
