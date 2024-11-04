<?php

namespace Oro\Bundle\AuthorizeNetBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\AuthorizeNetBundle\Entity\Repository\AuthorizeNetSettingsRepository;
use Oro\Bundle\IntegrationBundle\Entity\Transport;
use Oro\Bundle\LocaleBundle\Entity\LocalizedFallbackValue;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Entity which store AuthorizeNet integration settings
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
#[ORM\Entity(repositoryClass: AuthorizeNetSettingsRepository::class)]
class AuthorizeNetSettings extends Transport
{
    const API_LOGIN_ID = 'api_login_id';
    const TRANSACTION_KEY = 'transaction_key';
    const CLIENT_KEY = 'client_key';
    const CREDIT_CARD_LABELS_KEY = 'credit_card_labels';
    const CREDIT_CARD_SHORT_LABELS_KEY = 'credit_card_short_labels';
    const CREDIT_CARD_PAYMENT_ACTION_KEY = 'credit_card_payment_action';
    const ALLOWED_CREDIT_CARD_TYPES_KEY = 'allowed_credit_card_types';
    const REQUIRE_CVV_ENTRY_KEY = 'require_cvv_entry';
    const TEST_MODE_KEY = 'test_mode';
    const ENABLED_CIM_KEY = 'enabled_cim';
    const ENABLED_CIM_WEBSITES_KEY = 'enabled_cim_websites';
    const ECHECK_ENABLED_KEY = 'echeck_enabled';
    const ECHECK_LABELS_KEY = 'echeck_labels';
    const ECHECK_SHORT_LABELS_KEY = 'echeck_short_labels';
    const ECHECK_ACCOUNT_TYPES_KEY = 'echeck_account_types';
    const ECHECK_CONFIRMATION_TEXT_KEY = 'echeck_confirmation_text';
    const ALLOW_HOLD_TRANSACTION = 'allow_hold_transaction';

    const ECHECK_ACCOUNT_TYPES = [
        'checking',
        'savings',
        'businessChecking'
    ];

    /**
     * @var ParameterBag
     */
    protected $settings;

    #[ORM\Column(name: 'au_net_api_login', type: Types::STRING, length: 255, nullable: false)]
    protected ?string $apiLoginId = null;

    #[ORM\Column(name: 'au_net_transaction_key', type: Types::STRING, length: 255, nullable: false)]
    protected ?string $transactionKey = null;

    #[ORM\Column(name: 'au_net_client_key', type: Types::STRING, length: 255, nullable: false)]
    protected ?string $clientKey = null;

    #[ORM\Column(name: 'au_net_credit_card_action', type: Types::STRING, length: 255, nullable: false)]
    protected ?string $creditCardPaymentAction = null;

    /**
     * @var array
     */
    #[ORM\Column(name: 'au_net_allowed_card_types', type: Types::ARRAY, length: 255, nullable: false)]
    protected $allowedCreditCardTypes = [];

    /**
     * @var Collection<int, LocalizedFallbackValue>
     */
    #[ORM\ManyToMany(targetEntity: LocalizedFallbackValue::class, cascade: ['ALL'], orphanRemoval: true)]
    #[ORM\JoinTable(name: 'oro_au_net_credit_card_lbl')]
    #[ORM\JoinColumn(name: 'transport_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'localized_value_id', referencedColumnName: 'id', unique: true, onDelete: 'CASCADE')]
    protected ?Collection $creditCardLabels = null;

    /**
     * @var Collection<int, LocalizedFallbackValue>
     */
    #[ORM\ManyToMany(targetEntity: LocalizedFallbackValue::class, cascade: ['ALL'], orphanRemoval: true)]
    #[ORM\JoinTable(name: 'oro_au_net_credit_card_sh_lbl')]
    #[ORM\JoinColumn(name: 'transport_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'localized_value_id', referencedColumnName: 'id', unique: true, onDelete: 'CASCADE')]
    protected ?Collection $creditCardShortLabels = null;

    #[ORM\Column(name: 'au_net_test_mode', type: Types::BOOLEAN, options: ['default' => false])]
    protected ?bool $authNetTestMode = false;

    #[ORM\Column(name: 'au_net_require_cvv_entry', type: Types::BOOLEAN, options: ['default' => true])]
    protected ?bool $authNetRequireCVVEntry = true;

    #[ORM\Column(name: 'au_net_enabled_cim', type: Types::BOOLEAN, options: ['default' => false])]
    protected ?bool $enabledCIM = false;

    /**
     * @var Collection<int, Website>
     */
    #[ORM\ManyToMany(targetEntity: Website::class)]
    #[ORM\JoinTable(name: 'oro_au_net_enabled_cim_website')]
    #[ORM\JoinColumn(name: 'transport_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'website_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected $enabledCIMWebsites;

    #[ORM\Column(name: 'au_net_echeck_enabled', type: Types::BOOLEAN, options: ['default' => false])]
    protected ?bool $eCheckEnabled = false;

    /**
     * @var Collection<int, LocalizedFallbackValue>
     */
    #[ORM\ManyToMany(targetEntity: LocalizedFallbackValue::class, cascade: ['ALL'], orphanRemoval: true)]
    #[ORM\JoinTable(name: 'oro_au_net_echeck_label')]
    #[ORM\JoinColumn(name: 'transport_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'localized_value_id', referencedColumnName: 'id', unique: true, onDelete: 'CASCADE')]
    protected ?Collection $eCheckLabels = null;

    /**
     * @var Collection<int, LocalizedFallbackValue>
     */
    #[ORM\ManyToMany(targetEntity: LocalizedFallbackValue::class, cascade: ['ALL'], orphanRemoval: true)]
    #[ORM\JoinTable(name: 'oro_au_net_echeck_short_label')]
    #[ORM\JoinColumn(name: 'transport_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'localized_value_id', referencedColumnName: 'id', unique: true, onDelete: 'CASCADE')]
    protected ?Collection $eCheckShortLabels = null;

    /**
     * @var array
     */
    #[ORM\Column(name: 'au_net_echeck_account_types', type: Types::ARRAY)]
    protected $eCheckAccountTypes = self::ECHECK_ACCOUNT_TYPES;

    #[ORM\Column(name: 'au_net_echeck_confirmation_txt', type: Types::TEXT)]
    protected ?string $eCheckConfirmationText = null;

    #[ORM\Column(name: 'au_net_allow_hold_transaction', type: Types::BOOLEAN, options: ['default' => true])]
    protected ?bool $allowHoldTransaction = true;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->creditCardLabels = new ArrayCollection();
        $this->creditCardShortLabels = new ArrayCollection();
        $this->enabledCIMWebsites = new ArrayCollection();
        $this->eCheckLabels = new ArrayCollection();
        $this->eCheckShortLabels = new ArrayCollection();
    }

    #[\Override]
    public function getSettingsBag()
    {
        if (null === $this->settings) {
            $this->settings = new ParameterBag([
                self::API_LOGIN_ID => $this->getApiLoginId(),
                self::TRANSACTION_KEY => $this->getTransactionKey(),
                self::CLIENT_KEY => $this->getClientKey(),
                self::CREDIT_CARD_LABELS_KEY => $this->getCreditCardLabels(),
                self::CREDIT_CARD_SHORT_LABELS_KEY => $this->getCreditCardShortLabels(),
                self::CREDIT_CARD_PAYMENT_ACTION_KEY => $this->getCreditCardPaymentAction(),
                self::ALLOWED_CREDIT_CARD_TYPES_KEY => $this->getAllowedCreditCardTypes(),
                self::TEST_MODE_KEY => $this->getAuthNetTestMode(),
                self::REQUIRE_CVV_ENTRY_KEY => $this->getAuthNetRequireCVVEntry(),
                self::ENABLED_CIM_KEY => $this->isEnabledCIM(),
                self::ENABLED_CIM_WEBSITES_KEY => $this->getEnabledCIMWebsites(),
                self::ECHECK_ENABLED_KEY => $this->isECheckEnabled(),
                self::ECHECK_LABELS_KEY => $this->getECheckLabels(),
                self::ECHECK_SHORT_LABELS_KEY => $this->getECheckShortLabels(),
                self::ECHECK_ACCOUNT_TYPES_KEY => $this->getECheckAccountTypes(),
                self::ECHECK_CONFIRMATION_TEXT_KEY => $this->getECheckConfirmationText(),
                self::ALLOW_HOLD_TRANSACTION => $this->isAllowHoldTransaction()
            ]);
        }

        return $this->settings;
    }

    /**
     * @return string
     */
    public function getCreditCardPaymentAction()
    {
        return $this->creditCardPaymentAction;
    }

    /**
     * @param string $creditCardPaymentAction
     */
    public function setCreditCardPaymentAction($creditCardPaymentAction)
    {
        $this->creditCardPaymentAction = $creditCardPaymentAction;
    }

    /**
     * @return array
     */
    public function getAllowedCreditCardTypes()
    {
        return $this->allowedCreditCardTypes;
    }

    public function setAllowedCreditCardTypes(array $allowedCreditCardTypes)
    {
        $this->allowedCreditCardTypes = $allowedCreditCardTypes;
    }

    /**
     * @return bool
     */
    public function getAuthNetTestMode()
    {
        return $this->authNetTestMode;
    }

    /**
     * @param bool $testMode
     */
    public function setAuthNetTestMode($testMode)
    {
        $this->authNetTestMode = (bool)$testMode;
    }

    /**
     * Add creditCardLabel
     *
     * @param LocalizedFallbackValue $creditCardLabel
     *
     * @return AuthorizeNetSettings
     */
    public function addCreditCardLabel(LocalizedFallbackValue $creditCardLabel)
    {
        if (!$this->creditCardLabels->contains($creditCardLabel)) {
            $this->creditCardLabels->add($creditCardLabel);
        }

        return $this;
    }

    /**
     * Remove creditCardLabel
     *
     * @param LocalizedFallbackValue $creditCardLabel
     *
     * @return AuthorizeNetSettings
     */
    public function removeCreditCardLabel(LocalizedFallbackValue $creditCardLabel)
    {
        if ($this->creditCardLabels->contains($creditCardLabel)) {
            $this->creditCardLabels->removeElement($creditCardLabel);
        }

        return $this;
    }

    /**
     * Get creditCardLabels
     *
     * @return Collection
     */
    public function getCreditCardLabels()
    {
        return $this->creditCardLabels;
    }

    /**
     * Add creditCardShortLabel
     *
     * @param LocalizedFallbackValue $creditCardShortLabel
     *
     * @return AuthorizeNetSettings
     */
    public function addCreditCardShortLabel(LocalizedFallbackValue $creditCardShortLabel)
    {
        if (!$this->creditCardShortLabels->contains($creditCardShortLabel)) {
            $this->creditCardShortLabels->add($creditCardShortLabel);
        }

        return $this;
    }

    /**
     * Remove creditCardShortLabel
     *
     * @param LocalizedFallbackValue $creditCardShortLabel
     *
     * @return AuthorizeNetSettings
     */
    public function removeCreditCardShortLabel(LocalizedFallbackValue $creditCardShortLabel)
    {
        if ($this->creditCardShortLabels->contains($creditCardShortLabel)) {
            $this->creditCardShortLabels->removeElement($creditCardShortLabel);
        }

        return $this;
    }

    /**
     * Get creditCardShortLabels
     *
     * @return Collection
     */
    public function getCreditCardShortLabels()
    {
        return $this->creditCardShortLabels;
    }

    /**
     * @return string
     */
    public function getTransactionKey()
    {
        return $this->transactionKey;
    }

    /**
     * @param string $transactionKey
     */
    public function setTransactionKey($transactionKey)
    {
        $this->transactionKey = $transactionKey;
    }

    /**
     * @return string
     */
    public function getClientKey()
    {
        return $this->clientKey;
    }

    /**
     * @param string $clientKey
     */
    public function setClientKey($clientKey)
    {
        $this->clientKey = $clientKey;
    }
    /**
     * @return string
     */
    public function getApiLoginId()
    {
        return $this->apiLoginId;
    }

    /**
     * @param string $apiLoginId
     */
    public function setApiLoginId($apiLoginId)
    {
        $this->apiLoginId = $apiLoginId;
    }

    /**
     * @param boolean $requireCVVEntry
     *
     * @return AuthorizeNetSettings
     */
    public function setAuthNetRequireCVVEntry($requireCVVEntry)
    {
        $this->authNetRequireCVVEntry = (bool)$requireCVVEntry;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getAuthNetRequireCVVEntry()
    {
        return $this->authNetRequireCVVEntry;
    }

    public function isEnabledCIM(): bool
    {
        return $this->enabledCIM;
    }

    public function setEnabledCIM(bool $enabledCIM)
    {
        $this->enabledCIM = $enabledCIM;
    }

    /**
     * @return Collection|Website[]
     */
    public function getEnabledCIMWebsites()
    {
        return $this->enabledCIMWebsites;
    }

    /**
     * @param array|Website[] $enabledCIMWebsites
     */
    public function setEnabledCIMWebsites($enabledCIMWebsites)
    {
        $this->enabledCIMWebsites = $enabledCIMWebsites;
    }

    public function isECheckEnabled(): bool
    {
        return $this->eCheckEnabled;
    }

    /**
     * @param bool $eCheckEnabled
     * @return $this
     */
    public function setECheckEnabled(bool $eCheckEnabled): AuthorizeNetSettings
    {
        $this->eCheckEnabled = $eCheckEnabled;

        return $this;
    }

    /**
     * @return Collection|LocalizedFallbackValue[]
     */
    public function getECheckLabels()
    {
        return $this->eCheckLabels;
    }

    /**
     * @param LocalizedFallbackValue $eCheckLabel
     * @return $this
     */
    public function addECheckLabel(LocalizedFallbackValue $eCheckLabel)
    {
        if (!$this->eCheckLabels->contains($eCheckLabel)) {
            $this->eCheckLabels->add($eCheckLabel);
        }

        return $this;
    }

    /**
     * @param LocalizedFallbackValue $eCheckLabel
     * @return $this
     */
    public function removeECheckLabel(LocalizedFallbackValue $eCheckLabel)
    {
        if ($this->eCheckLabels->contains($eCheckLabel)) {
            $this->eCheckLabels->removeElement($eCheckLabel);
        }

        return $this;
    }

    /**
     * @return Collection|LocalizedFallbackValue[]
     */
    public function getECheckShortLabels()
    {
        return $this->eCheckShortLabels;
    }

    /**
     * @param LocalizedFallbackValue $eCheckLabel
     * @return $this
     */
    public function addECheckShortLabel(LocalizedFallbackValue $eCheckLabel)
    {
        if (!$this->eCheckShortLabels->contains($eCheckLabel)) {
            $this->eCheckShortLabels->add($eCheckLabel);
        }

        return $this;
    }

    /**
     * @param LocalizedFallbackValue $eCheckLabel
     * @return $this
     */
    public function removeECheckShortLabel(LocalizedFallbackValue $eCheckLabel)
    {
        if ($this->eCheckShortLabels->contains($eCheckLabel)) {
            $this->eCheckShortLabels->removeElement($eCheckLabel);
        }

        return $this;
    }

    public function getECheckAccountTypes(): ?array
    {
        return $this->eCheckAccountTypes;
    }

    /**
     * @param array $eCheckAccountTypes
     * @return $this
     */
    public function setECheckAccountTypes(array $eCheckAccountTypes): AuthorizeNetSettings
    {
        $this->eCheckAccountTypes = $eCheckAccountTypes;

        return $this;
    }

    public function getECheckConfirmationText(): ?string
    {
        return $this->eCheckConfirmationText;
    }

    public function setECheckConfirmationText(?string $eCheckConfirmationText): AuthorizeNetSettings
    {
        $this->eCheckConfirmationText = $eCheckConfirmationText;

        return $this;
    }

    public function isAllowHoldTransaction(): bool
    {
        return (bool) $this->allowHoldTransaction;
    }

    public function setAllowHoldTransaction(bool $allowHoldTransaction): AuthorizeNetSettings
    {
        $this->allowHoldTransaction = $allowHoldTransaction;

        return $this;
    }
}
