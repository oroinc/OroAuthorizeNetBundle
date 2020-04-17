<?php

namespace Oro\Bundle\AuthorizeNetBundle\Method\Option\Resolver;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;
use Oro\Bundle\AuthorizeNetBundle\Method\Config\AuthorizeNetConfigInterface;
use Oro\Bundle\AuthorizeNetBundle\Method\Option\Provider\AddressInfoProvider;
use Oro\Bundle\AuthorizeNetBundle\Method\Option\Provider\CustomerProfileOptionProviderInterface;
use Oro\Bundle\AuthorizeNetBundle\Method\Option\Provider\Factory\MethodOptionProviderFactory;
use Oro\Bundle\AuthorizeNetBundle\Method\Option\Provider\HttpRequestOptionProviderInterface;
use Oro\Bundle\AuthorizeNetBundle\Method\Option\Provider\InternalOptionProviderInterface;
use Oro\Bundle\AuthorizeNetBundle\Method\Option\Provider\MerchantOptionProviderInterface;
use Oro\Bundle\AuthorizeNetBundle\Method\Option\Provider\MethodOptionProvider;
use Oro\Bundle\AuthorizeNetBundle\Method\Option\Provider\MethodOptionProviderInterface;
use Oro\Bundle\AuthorizeNetBundle\Method\Option\Provider\OpaqueOptionProviderInterface;
use Oro\Bundle\AuthorizeNetBundle\Method\Option\Provider\PaymentOptionProviderInterface;
use Oro\Bundle\AuthorizeNetBundle\Method\Option\Provider\TaxProvider;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;

/**
 * Resolves options, that will be passed to Gateway, differently, depends on specific flow
 * Charge credit card
 * Charge Customer profile
 * Create Profile
 * Capture
 * etc...
 */
class MethodOptionResolver implements MethodOptionResolverInterface
{
    /** @var MethodOptionProviderFactory */
    private $optionProviderFactory;

    /**
     * MethodOptionResolver constructor.
     * @param MethodOptionProviderFactory $optionProviderFactory
     */
    public function __construct(MethodOptionProviderFactory $optionProviderFactory)
    {
        $this->optionProviderFactory = $optionProviderFactory;
    }

    /**
     * @param AuthorizeNetConfigInterface $config
     * @param PaymentTransaction $transaction
     * @return MethodOptionProviderInterface
     */
    protected function createOptionProvider(
        AuthorizeNetConfigInterface $config,
        PaymentTransaction $transaction
    ): MethodOptionProviderInterface {
        return $this->optionProviderFactory->createMethodOptionProvider($config, $transaction);
    }

    /**
     * @param AuthorizeNetConfigInterface $config
     * @param PaymentTransaction $transaction
     * @return array
     */
    public function resolvePurchase(AuthorizeNetConfigInterface $config, PaymentTransaction $transaction): array
    {
        $addressInfoProvider = $this->optionProviderFactory->createAddressProvider($transaction);
        $taxProvider = $this->optionProviderFactory->createTaxProvider($transaction);
        $provider = $this->createOptionProvider($config, $transaction);

        $options = array_merge(
            $this->getMerchantOptions($provider),
            $this->getPaymentOptions($provider),
            $this->getCardCodeOptions($provider),
            $this->getShipToOptions($addressInfoProvider),
            $this->getLineItemsOptions($provider),
            $this->getInvoiceNumberOptions($provider),
            $this->getTaxAmountOptions($taxProvider),
            $this->getCustomerIpOptions($provider)
        );

        if ($this->isOpaqueFlow($provider)) {
            //merge billing address options (required for credit card processing)
            $options = array_merge($options, $this->getBillToOptions($addressInfoProvider));
            return $this->opaqueFlow($options, $provider);
        }

        if (!$provider->isCIMEnabled()) {
            throw new \LogicException('CIM must be enabled for profile payments');
        }

        $options = $this->withExistCustomerPaymentProfileId($options, $provider);

        return $this->withExistCustomerProfileId($options, $provider);
    }

    /**
     * @param array $options
     * @param MethodOptionProviderInterface $methodOptionsProvider
     * @return array
     */
    protected function opaqueFlow(array $options, MethodOptionProviderInterface $methodOptionsProvider): array
    {
        $options = $this->withOpaque($options, $methodOptionsProvider);
        if (!$this->shouldCreateProfile($methodOptionsProvider)) {
            return $options;
        }

        $options = $this->withCreateProfile($options, $methodOptionsProvider);
        if ($this->isCustomerProfileExists($methodOptionsProvider)) {
            return $this->withExistCustomerProfileId($options, $methodOptionsProvider);
        }

        $options = $this->withEmail($options, $methodOptionsProvider);

        return $this->withGenerateCustomerId($options, $methodOptionsProvider);
    }

    /**
     * @param AuthorizeNetConfigInterface $config
     * @param PaymentTransaction $transaction
     * @return array
     */
    public function resolveAuthorize(AuthorizeNetConfigInterface $config, PaymentTransaction $transaction): array
    {
        $provider = $this->createOptionProvider($config, $transaction);

        $options = array_merge(
            $this->getMerchantOptions($provider),
            $this->getTransactionRequestOptions($transaction)
        );

        return $options;
    }

    /**
     * @param AuthorizeNetConfigInterface $config
     * @param PaymentTransaction $transaction
     * @return array
     */
    public function resolveCharge(AuthorizeNetConfigInterface $config, PaymentTransaction $transaction): array
    {
        $provider = $this->createOptionProvider($config, $transaction);

        $options = array_merge(
            $this->getMerchantOptions($provider),
            $this->getTransactionRequestOptions($transaction)
        );

        return $options;
    }

    /**
     * @param AuthorizeNetConfigInterface $config
     * @param PaymentTransaction $transaction
     * @return array
     */
    public function resolveCapture(AuthorizeNetConfigInterface $config, PaymentTransaction $transaction): array
    {
        $authorizeTransaction = $transaction->getSourcePaymentTransaction();
        if (!$authorizeTransaction) {
            throw new \LogicException('Source transaction is required for capture');
        }

        $authProvider = $this->createOptionProvider($config, $authorizeTransaction);

        $options = array_merge(
            $this->getPaymentOptions($authProvider),
            $this->getOriginalTransactionOptions($authProvider),
            $this->getMerchantOptions($this->createOptionProvider($config, $transaction)),
            $this->getTransactionRequestOptions($transaction)
        );

        return $options;
    }

    /**
     * @param PaymentOptionProviderInterface $provider
     * @return array
     */
    protected function getPaymentOptions(PaymentOptionProviderInterface $provider): array
    {
        $options = [];
        $options[Option\Amount::AMOUNT] = $provider->getAmount();
        $options[Option\Currency::CURRENCY] = $provider->getCurrency();

        return $options;
    }

    /**
     * @param PaymentOptionProviderInterface $provider
     * @return array
     */
    protected function getOriginalTransactionOptions(PaymentOptionProviderInterface $provider): array
    {
        $options = [];
        $options[Option\OriginalTransaction::ORIGINAL_TRANSACTION] = $provider->getOriginalTransaction();

        return $options;
    }

    /**
     * @param array $options
     * @param OpaqueOptionProviderInterface $provider
     * @return array
     */
    protected function withOpaque(array $options, OpaqueOptionProviderInterface $provider)
    {
        $options[Option\DataDescriptor::DATA_DESCRIPTOR] = $provider->getDataDescriptor();
        $options[Option\DataValue::DATA_VALUE] = $provider->getDataValue();
        $options[Option\ChargeType::NAME] = Option\ChargeType::TYPE_CREDIT_CARD;

        return $options;
    }

    /**
     * @param array $options
     * @param CustomerProfileOptionProviderInterface $provider
     * @return array
     */
    protected function withExistCustomerProfileId(
        array $options,
        CustomerProfileOptionProviderInterface $provider
    ): array {
        $options[Option\CustomerProfileId::CUSTOMER_PROFILE_ID] = $provider->getExistingCustomerProfileId();

        return $options;
    }

    /**
     * @param array $options
     * @param CustomerProfileOptionProviderInterface $provider
     * @return array
     */
    protected function withExistCustomerPaymentProfileId(
        array $options,
        CustomerProfileOptionProviderInterface $provider
    ): array {
        $options[Option\CustomerPaymentProfileId::CUSTOMER_PAYMENT_PROFILE_ID]
            = $provider->getExistingCustomerPaymentProfileId();
        $options[Option\ChargeType::NAME] = Option\ChargeType::TYPE_PAYMENT_PROFILE;

        return $options;
    }

    /**
     * @param array $options
     * @param CustomerProfileOptionProviderInterface $provider
     * @return array
     */
    protected function withGenerateCustomerId(array $options, CustomerProfileOptionProviderInterface $provider): array
    {
        $options[Option\CustomerDataId::NAME] = $provider->getGeneratedNewCustomerProfileId();

        return $options;
    }

    /**
     * @param array $options
     * @param CustomerProfileOptionProviderInterface $provider
     * @return array
     */
    protected function withEmail(array $options, CustomerProfileOptionProviderInterface $provider): array
    {
        $email = $provider->getEmail();
        if (null !== $email) {
            $options[Option\Email::EMAIL] = $email;
        }

        return $options;
    }

    /**
     * @param array $options
     * @param InternalOptionProviderInterface $provider
     * @return array
     */
    protected function withCreateProfile(array $options, InternalOptionProviderInterface $provider): array
    {
        if (false === $this->shouldCreateProfile($provider)) {
            throw new \LogicException('No createProfile option');
        }

        $options[Option\CreateProfile::NAME] = $provider->getCreateProfile();

        return $options;
    }

    /**
     * @param InternalOptionProviderInterface $provider
     * @return array
     */
    protected function getCardCodeOptions(InternalOptionProviderInterface $provider): array
    {
        $options = [];
        $cardCode = $provider->getCardCode();
        if ($cardCode) {
            $options[Option\CardCode::NAME] = $provider->getCardCode();
        }

        return $options;
    }

    /**
     * @param MerchantOptionProviderInterface $provider
     * @return array
     */
    protected function getMerchantOptions(MerchantOptionProviderInterface $provider): array
    {
        $options = [];
        $options[Option\ApiLoginId::API_LOGIN_ID] = $provider->getApiLoginId();
        $options[Option\TransactionKey::TRANSACTION_KEY] = $provider->getTransactionKey();

        $solutionId = $provider->getSolutionId();
        if ($solutionId) {
            $options[Option\SolutionId::SOLUTION_ID] = $solutionId;
        }

        return $options;
    }

    /**
     * @param AddressInfoProvider $provider
     * @return array
     */
    protected function getBillToOptions(AddressInfoProvider $provider): array
    {
        $options = [];
        $address = $provider->getBillingAddressDto();
        if ($address) {
            $options[Option\Address\FirstName::FIRST_NAME] = (string) $address->getFirstName();
            $options[Option\Address\LastName::LAST_NAME] = (string) $address->getLastName();
            $options[Option\Address\Company::COMPANY] = (string) $address->getCompany();
            $options[Option\Address\Address::ADDRESS] = (string) $address->getStreet();
            $options[Option\Address\Country::COUNTRY] = (string) $address->getCountryCode();
            $options[Option\Address\State::STATE] = (string) $address->getRegionString();
            $options[Option\Address\City::CITY] = (string) $address->getCity();
            $options[Option\Address\Zip::ZIP] = (string) $address->getZip();
            $options[Option\Address\PhoneNumber::PHONE_NUMBER] = (string) $address->getPhoneNumber();
            $options[Option\Address\FaxNumber::FAX_NUMBER] = (string) $address->getFaxNumber();
        }

        return $options;
    }

    /**
     * @param PaymentTransaction $transaction
     * @return array
     */
    protected function getTransactionRequestOptions(PaymentTransaction $transaction): array
    {
        return $transaction->getRequest();
    }

    /**
     * @param InternalOptionProviderInterface $provider
     * @return bool
     */
    protected function isOpaqueFlow(InternalOptionProviderInterface $provider): bool
    {
        return $provider->getProfileId() === null;
    }

    /**
     * @param InternalOptionProviderInterface $provider
     * @return bool
     */
    protected function shouldCreateProfile(InternalOptionProviderInterface $provider): bool
    {
        return $provider->getCreateProfile() === true && $provider->isCIMEnabled();
    }

    /**
     * @param CustomerProfileOptionProviderInterface $provider
     * @return bool
     */
    protected function isCustomerProfileExists(CustomerProfileOptionProviderInterface $provider): bool
    {
        return $provider->isCustomerProfileExists();
    }

    /**
     * @param AddressInfoProvider $provider
     * @return array
     */
    protected function getShipToOptions(AddressInfoProvider $provider): array
    {
        $options = [];
        $address = $provider->getShippingAddressDto();
        if ($address) {
            $options[Option\ShippingAddress::FIRST_NAME] = (string) $address->getFirstName();
            $options[Option\ShippingAddress::LAST_NAME] = (string) $address->getLastName();
            $options[Option\ShippingAddress::COMPANY] = (string) $address->getCompany();
            $options[Option\ShippingAddress::ADDRESS] = (string) $address->getStreet();
            $options[Option\ShippingAddress::COUNTRY] = (string) $address->getCountryCode();
            $options[Option\ShippingAddress::STATE] = (string) $address->getRegionString();
            $options[Option\ShippingAddress::CITY] = (string) $address->getCity();
            $options[Option\ShippingAddress::ZIP] = (string) $address->getZip();
        }

        return $options;
    }

    /**
     * @param MethodOptionProvider $provider
     * @return array
     */
    protected function getLineItemsOptions(MethodOptionProvider $provider): array
    {
        $options = [];
        $lineItems = $provider->getLineItems();

        if ($lineItems) {
            $options[Option\LineItems::NAME] = $lineItems;
        }

        return $options;
    }

    /**
     * @param MethodOptionProvider $provider
     * @return array
     */
    protected function getInvoiceNumberOptions(MethodOptionProvider $provider): array
    {
        $options = [];
        $invoiceNumber = $provider->getInvoiceNumber();

        if ($invoiceNumber) {
            $options[Option\InvoiceNumber::NAME] = $invoiceNumber;
        }

        return $options;
    }

    /**
     * @param TaxProvider $provider
     * @return array
     */
    protected function getTaxAmountOptions(TaxProvider $provider): array
    {
        $options = [];
        $taxAmount = $provider->getTaxAmount();

        if ($taxAmount) {
            $options[Option\TaxAmount::NAME] = $taxAmount;
        }

        return $options;
    }

    /**
     * @param HttpRequestOptionProviderInterface $provider
     * @return array
     */
    protected function getCustomerIpOptions(HttpRequestOptionProviderInterface $provider): array
    {
        $options = [];
        $ip = $provider->getClientIp();
        if ($ip) {
            $options[Option\CustomerIp::NAME] = $ip;
        }

        return $options;
    }
}
