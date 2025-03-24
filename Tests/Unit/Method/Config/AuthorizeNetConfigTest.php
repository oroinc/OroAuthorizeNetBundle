<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\Method\Config;

use Oro\Bundle\AuthorizeNetBundle\Method\Config\AuthorizeNetConfig;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class AuthorizeNetConfigTest extends TestCase
{
    private AuthorizeNetConfig $config;

    #[\Override]
    protected function setUp(): void
    {
        $this->config = new AuthorizeNetConfig([
            AuthorizeNetConfig::FIELD_PAYMENT_METHOD_IDENTIFIER => 'test_payment_method_identifier',
            AuthorizeNetConfig::FIELD_ADMIN_LABEL => 'test admin label',
            AuthorizeNetConfig::FIELD_LABEL => 'test label',
            AuthorizeNetConfig::FIELD_SHORT_LABEL => 'test short label',
            AuthorizeNetConfig::ALLOWED_CREDIT_CARD_TYPES_KEY => ['Master Card', 'Visa'],
            AuthorizeNetConfig::TEST_MODE_KEY => true,
            AuthorizeNetConfig::PURCHASE_ACTION_KEY => 'authorize',
            AuthorizeNetConfig::CLIENT_KEY => 'client key',
            AuthorizeNetConfig::API_LOGIN_ID => 'api login id',
            AuthorizeNetConfig::TRANSACTION_KEY => 'trans key',
            AuthorizeNetConfig::INTEGRATION_ID => 4,
            AuthorizeNetConfig::ECHECK_ENABLED => true,
            AuthorizeNetConfig::ALLOW_HOLD_TRANSACTION => true
        ]);
    }

    public function testGetLabel(): void
    {
        self::assertSame('test label', $this->config->getLabel());
    }

    public function testGetShortLabel(): void
    {
        self::assertSame('test short label', $this->config->getShortLabel());
    }

    public function testGetAdminLabel(): void
    {
        self::assertSame('test admin label', $this->config->getAdminLabel());
    }

    public function testGetPaymentMethodIdentifier(): void
    {
        self::assertSame('test_payment_method_identifier', $this->config->getPaymentMethodIdentifier());
    }

    public function testIsTestMode(): void
    {
        self::assertTrue($this->config->isTestMode());
    }

    public function testGetPurchaseAction(): void
    {
        self::assertSame('authorize', $this->config->getPurchaseAction());
    }

    public function testGetAllowedCreditCards(): void
    {
        self::assertSame(['Master Card', 'Visa'], $this->config->getAllowedCreditCards());
    }

    public function testGetApiLoginId(): void
    {
        self::assertSame('api login id', $this->config->getApiLoginId());
    }

    public function testGetTransactionKey(): void
    {
        self::assertSame('trans key', $this->config->getTransactionKey());
    }

    public function testGetClientKey(): void
    {
        self::assertSame('client key', $this->config->getClientKey());
    }

    public function testGetIntegrationId(): void
    {
        self::assertSame(4, $this->config->getIntegrationId());
    }

    public function testIsECheckEnabled(): void
    {
        self::assertTrue($this->config->isECheckEnabled());
    }

    public function testIsAllowHoldTransaction(): void
    {
        self::assertTrue($this->config->isAllowHoldTransaction());
    }
}
