<?php

namespace Oro\Bundle\AuthorizeNetBundle\Method\Config;

use Doctrine\Common\Collections\Collection;
use Oro\Bundle\PaymentBundle\Method\Config\PaymentConfigInterface;
use Oro\Bundle\WebsiteBundle\Entity\Website;

/**
 * Represents Authorize.Net integration parameters as DTO Interface
 * required to manage prepare/send requests to Authorize.Net flow
 */
interface AuthorizeNetConfigInterface extends
    PaymentConfigInterface,
    TransactionHoldConfigInterface
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

    /**
     * @return int
     */
    public function getIntegrationId(): int;

    /**
     * @return bool
     */
    public function isECheckEnabled(): bool;

    /**
     * @return array
     */
    public function getECheckAccountTypes(): array;

    /**
     * @return string
     */
    public function getECheckConfirmationText(): string;
}
