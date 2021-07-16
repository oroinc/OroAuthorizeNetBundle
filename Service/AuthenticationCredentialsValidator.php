<?php

namespace Oro\Bundle\AuthorizeNetBundle\Service;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Gateway;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Request\AuthenticateTestRequest;

/**
 * Wrapper for authenticateTestRequest
 */
class AuthenticationCredentialsValidator
{
    /** @var Gateway */
    private $gateway;

    public function __construct(Gateway $gateway)
    {
        $this->gateway = $gateway;
    }

    public function isValid(string $apiLogin, string $transactionKey, bool $isTestMode): bool
    {
        $this->gateway->setTestMode($isTestMode);

        $response = $this->gateway
            ->request(
                AuthenticateTestRequest::REQUEST_TYPE,
                [
                    Option\ApiLoginId::API_LOGIN_ID => $apiLogin,
                    Option\TransactionKey::TRANSACTION_KEY => $transactionKey,
                ]
            );

        return $response->isSuccessful();
    }
}
