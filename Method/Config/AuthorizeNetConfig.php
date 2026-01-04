<?php

namespace Oro\Bundle\AuthorizeNetBundle\Method\Config;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\PaymentBundle\Method\Config\ParameterBag\AbstractParameterBagPaymentConfig;

/**
 * Represents Authorize.Net integration parameters as DTO
 * required to manage prepare/send requests to Authorize.Net flow
 */
class AuthorizeNetConfig extends AbstractParameterBagPaymentConfig implements AuthorizeNetConfigInterface
{
    public const ALLOWED_CREDIT_CARD_TYPES_KEY = 'allowed_credit_card_types';
    public const PURCHASE_ACTION_KEY  = 'purchase_action';
    public const TEST_MODE_KEY  = 'test_mode';
    public const API_LOGIN_ID = 'api_login_id';
    public const TRANSACTION_KEY = 'transaction_key';
    public const CLIENT_KEY = 'client_key';
    public const REQUIRE_CVV_ENTRY_KEY = 'require_cvv_entry';
    public const ENABLED_CIM_KEY = 'enabled_cim';
    public const ENABLED_CIM_WEBSITES = 'enabled_cim_websites';
    public const INTEGRATION_ID = 'integration_id';
    public const ECHECK_ENABLED = 'echeck_enabled';
    public const ECHECK_ACCOUNT_TYPES = 'echeck_account_types';
    public const ECHECK_CONFIRMATION_TEXT = 'echeck_confirmation_text';
    public const ALLOW_HOLD_TRANSACTION = 'allow_hold_transaction';

    #[\Override]
    public function isTestMode()
    {
        return (bool)$this->get(self::TEST_MODE_KEY);
    }

    #[\Override]
    public function getAllowedCreditCards()
    {
        return (array)$this->get(self::ALLOWED_CREDIT_CARD_TYPES_KEY);
    }

    #[\Override]
    public function getPurchaseAction()
    {
        return (string)$this->get(self::PURCHASE_ACTION_KEY);
    }

    #[\Override]
    public function getApiLoginId()
    {
        return (string)$this->get(self::API_LOGIN_ID);
    }

    #[\Override]
    public function getTransactionKey()
    {
        return (string)$this->get(self::TRANSACTION_KEY);
    }

    #[\Override]
    public function getClientKey()
    {
        return (string)$this->get(self::CLIENT_KEY);
    }

    #[\Override]
    public function isRequireCvvEntryEnabled()
    {
        return (bool)$this->get(self::REQUIRE_CVV_ENTRY_KEY);
    }

    #[\Override]
    public function isEnabledCIM()
    {
        return (bool)$this->get(self::ENABLED_CIM_KEY);
    }

    #[\Override]
    public function getEnabledCIMWebsites()
    {
        return $this->get(self::ENABLED_CIM_WEBSITES, new ArrayCollection());
    }

    #[\Override]
    public function getIntegrationId(): int
    {
        return $this->getInt(self::INTEGRATION_ID);
    }

    #[\Override]
    public function isECheckEnabled(): bool
    {
        return $this->getBoolean(self::ECHECK_ENABLED);
    }

    #[\Override]
    public function getECheckAccountTypes(): array
    {
        return (array) $this->get(self::ECHECK_ACCOUNT_TYPES);
    }

    #[\Override]
    public function getECheckConfirmationText(): string
    {
        return (string) $this->get(self::ECHECK_CONFIRMATION_TEXT);
    }

    #[\Override]
    public function isAllowHoldTransaction(): bool
    {
        return (bool) $this->get(self::ALLOW_HOLD_TRANSACTION);
    }
}
