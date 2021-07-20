<?php

namespace Oro\Bundle\AuthorizeNetBundle\Method\Config;

use Doctrine\Common\Collections\Collection;
use Oro\Bundle\PaymentBundle\Method\Config\PaymentConfigInterface;
use Oro\Bundle\WebsiteBundle\Entity\Website;

/**
 * Represents Authorize.Net integration parameters as DTO Interface
 * required to manage prepare/send requests to Authorize.Net flow
 */
interface AuthorizeNetConfigInterface extends PaymentConfigInterface
{
    /**
     * @return string
     */
    public function getApiLoginId();

    /**
     * @return string
     */
    public function getTransactionKey();

    /**
     * @return string
     */
    public function getClientKey();

    /**
     * @return bool
     */
    public function isTestMode();

    /**
     * @return array
     */
    public function getAllowedCreditCards();

    /**
     * @return string
     */
    public function getPurchaseAction();

    /**
     * @return bool
     */
    public function isRequireCvvEntryEnabled();

    /**
     * @return bool
     */
    public function isEnabledCIM();

    /**
     * @return Collection|Website[]
     */
    public function getEnabledCIMWebsites();

    public function getIntegrationId(): int;

    public function isECheckEnabled(): bool;

    public function getECheckAccountTypes(): array;

    public function getECheckConfirmationText(): string;

    public function isAllowHoldTransaction(): bool;
}
