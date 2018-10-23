<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\Service;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Gateway;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Request\AuthenticateTestRequest;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Response\ResponseInterface;
use Oro\Bundle\AuthorizeNetBundle\Service\AuthenticationCredentialsValidator;

class AuthenticationCredentialsValidatorTest extends \PHPUnit\Framework\TestCase
{
    public function testIsValidSuccess()
    {
        list($apiLogin, $transactionKey) = ['test_api_login', 'test_transaction_key'];

        $gateway = $this->createGatewayMock($apiLogin, $transactionKey, true);
        $validator = new AuthenticationCredentialsValidator($gateway);

        self::assertTrue($validator->isValid($apiLogin, $transactionKey, true));
    }

    public function testIsValidFailed()
    {
        list($apiLogin, $transactionKey) = ['test_api_login', 'test_transaction_key'];

        $gateway = $this->createGatewayMock($apiLogin, $transactionKey, false);
        $validator = new AuthenticationCredentialsValidator($gateway);

        self::assertFalse($validator->isValid($apiLogin, $transactionKey, true));
    }

    private function createGatewayMock(string $apiLogin, string $transactionKey, bool $isResponseSuccessful)
    {
        $mock = $this->createMock(Gateway::class);

        $response = $this->createMock(ResponseInterface::class);
        $response
            ->expects($this->once())
            ->method('isSuccessful')
            ->willReturn($isResponseSuccessful);

        $mock
            ->expects($this->once())
            ->method('request')
            ->with(
                AuthenticateTestRequest::REQUEST_TYPE,
                [
                    Option\ApiLoginId::API_LOGIN_ID => $apiLogin,
                    Option\TransactionKey::TRANSACTION_KEY => $transactionKey,
                ]
            )
            ->willReturn($response);

        $mock
            ->expects($this->once())
            ->method('setTestMode')
            ->with(true);

        return $mock;
    }
}
