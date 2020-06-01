<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\Method\Config;

use Oro\Bundle\AuthorizeNetBundle\Method\Config\AuthorizeNetConfig;
use Oro\Bundle\AuthorizeNetBundle\Method\Config\AuthorizeNetConfigInterface;
use Oro\Bundle\PaymentBundle\Tests\Unit\Method\Config\AbstractPaymentConfigTestCase;

class AuthorizeNetConfigTest extends AbstractPaymentConfigTestCase
{
    /**
     * @var AuthorizeNetConfigInterface
     */
    protected $config;

    /**
     * {@inheritdoc}
     */
    protected function getPaymentConfig()
    {
        $params = [
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
        ];

        return new AuthorizeNetConfig($params);
    }

    public function testIsTestMode()
    {
        $this->assertTrue($this->config->isTestMode());
    }

    public function testGetPurchaseAction()
    {
        $this->assertSame('authorize', $this->config->getPurchaseAction());
    }

    public function testGetAllowedCreditCards()
    {
        $this->assertSame(['Master Card', 'Visa'], $this->config->getAllowedCreditCards());
    }

    public function testGetApiLoginId()
    {
        $this->assertSame('api login id', $this->config->getApiLoginId());
    }

    public function testGetTransactionKey()
    {
        $this->assertSame('trans key', $this->config->getTransactionKey());
    }

    public function testGetClientKey()
    {
        $this->assertSame('client key', $this->config->getClientKey());
    }

    public function testGetIntegrationId()
    {
        $this->assertSame(4, $this->config->getIntegrationId());
    }

    public function testIsECheckEnabled()
    {
        $this->assertTrue($this->config->isECheckEnabled());
    }

    public function testIsAllowHoldTransaction()
    {
        $this->assertTrue($this->config->isAllowHoldTransaction());
    }
}
