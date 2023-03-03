<?php

namespace Oro\Bundle\AuthorizeNetBundle\Helper;

use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Region;
use Oro\Bundle\AddressBundle\Entity\Repository\CountryRepository;
use Oro\Bundle\AddressBundle\Entity\Repository\RegionRepository;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Gateway;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option\Address as AddressOption;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Request;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Response\ResponseInterface;
use Oro\Bundle\AuthorizeNetBundle\Entity\CustomerPaymentProfile;
use Oro\Bundle\AuthorizeNetBundle\Entity\CustomerProfile;
use Oro\Bundle\AuthorizeNetBundle\Exception\CustomerPaymentProfileNotFoundException;
use Oro\Bundle\AuthorizeNetBundle\Method\Config\AuthorizeNetConfigInterface;
use Oro\Bundle\AuthorizeNetBundle\Model\DTO\PaymentProfileAddressDTO;
use Oro\Bundle\AuthorizeNetBundle\Model\DTO\PaymentProfileDTO;
use Oro\Bundle\AuthorizeNetBundle\Model\DTO\PaymentProfileEncodedDataDTO;
use Oro\Bundle\AuthorizeNetBundle\Model\DTO\PaymentProfileMaskedDataDTO;
use Oro\Bundle\AuthorizeNetBundle\Provider\CIMEnabledIntegrationConfigProvider;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\EntityExtendBundle\PropertyAccess;

/**
 * Request sender (helper to compose & send specific authorize net api request)
 */
class RequestSender
{
    /** @var Gateway */
    private $gateway;

    /** @var AuthorizeNetConfigInterface */
    private $config;

    /** @var DoctrineHelper */
    private $doctrineHelper;

    /** @var CIMEnabledIntegrationConfigProvider */
    private $configProvider;

    /** @var MerchantCustomerIdGenerator */
    private $merchantCustomerIdGenerator;

    public function __construct(
        Gateway $gateway,
        DoctrineHelper $doctrineHelper,
        CIMEnabledIntegrationConfigProvider $configProvider,
        MerchantCustomerIdGenerator $merchantCustomerIdGenerator
    ) {
        $this->gateway = $gateway;
        $this->doctrineHelper = $doctrineHelper;
        $this->configProvider = $configProvider;
        $this->merchantCustomerIdGenerator = $merchantCustomerIdGenerator;
    }

    /**
     * @param CustomerProfile $customerProfile
     * @return string
     */
    public function createCustomerProfile(CustomerProfile $customerProfile)
    {
        $merchantCustomerId = $this->merchantCustomerIdGenerator->generate(
            $customerProfile->getIntegration()->getId(),
            $customerProfile->getCustomerUser()->getId()
        );

        $options = array_merge(
            $this->buildAuthOptions(),
            [
                Option\Email::EMAIL => $customerProfile->getCustomerUser()->getEmail(),
                Option\MerchantCustomerId::MERCHANT_CUSTOMER_ID => $merchantCustomerId
            ]
        );

        $response = $this->sendRequest(Request\CreateCustomerProfileRequest::REQUEST_TYPE, $options);
        $this->checkResponse($response);
        $responseData = $response->getData();

        return $responseData['customer_profile_id'];
    }

    /**
     * @param PaymentProfileDTO $paymentProfileDTO
     * @return string
     */
    public function createCustomerPaymentProfile(PaymentProfileDTO $paymentProfileDTO)
    {
        $paymentProfile = $paymentProfileDTO->getProfile();
        $address = $paymentProfileDTO->getAddress();
        $encodedData = $paymentProfileDTO->getEncodedData();

        $options = array_merge(
            $this->buildAuthOptions(),
            $this->buildAddressOptions($address),
            $this->buildCustomerProfileIdOptions($paymentProfile->getCustomerProfile()),
            $this->buildIsDefaultOptions($paymentProfile),
            $this->buildValidationModeOptions(),
            $this->buildEncodedDataOptions($encodedData)
        );

        $response = $this->sendRequest(Request\CreateCustomerPaymentProfileRequest::REQUEST_TYPE, $options);
        $this->checkResponse($response);
        $responseData = $response->getData();

        return $responseData['customer_payment_profile_id'];
    }

    /**
     * @param PaymentProfileDTO $paymentProfileDTO
     * @return bool
     */
    public function updateCustomerPaymentProfile(PaymentProfileDTO $paymentProfileDTO)
    {
        $paymentProfile = $paymentProfileDTO->getProfile();
        $address = $paymentProfileDTO->getAddress();
        $encodedData = $paymentProfileDTO->getEncodedData();
        $maskedData = $paymentProfileDTO->getMaskedData();
        $updatePaymentData = $paymentProfileDTO->isUpdatePaymentData();

        $options = array_merge(
            $this->buildAuthOptions(),
            $this->buildAddressOptions($address),
            $this->buildCustomerProfileIdOptions($paymentProfile->getCustomerProfile()),
            $this->buildCustomerPaymentProfileIdOptions($paymentProfile),
            $this->buildIsDefaultOptions($paymentProfile),
            $this->buildValidationModeOptions(),
            [
                Option\UpdatePaymentData::UPDATE_PAYMENT_DATA => $updatePaymentData,
                Option\ProfileType::PROFILE_TYPE => $paymentProfile->getType()
            ]
        );

        if ($updatePaymentData) {
            $options = array_merge(
                $options,
                $this->buildEncodedDataOptions($encodedData)
            );
        } elseif ($paymentProfile->getType() === Option\ProfileType::CREDITCARD_TYPE) {
            $options[Option\CardNumber::CARD_NUMBER] = sprintf('XXXX%s', $paymentProfile->getLastDigits());
            $options[Option\ExpirationDate::EXPIRATION_DATE] = 'XXXX';
        } elseif ($paymentProfile->getType() === Option\ProfileType::ECHECK_TYPE) {
            $options[Option\AccountNumber::ACCOUNT_NUMBER] = $maskedData->getAccountNumber();
            $options[Option\RoutingNumber::ROUTING_NUMBER] = $maskedData->getRoutingNumber();
            $options[Option\NameOnAccount::NAME_ON_ACCOUNT] = $maskedData->getNameOnAccount();
            $options[Option\AccountType::ACCOUNT_TYPE] = $maskedData->getAccountType();
            $options[Option\BankName::BANK_NAME] = $maskedData->getBankName();
        }

        $response = $this->sendRequest(Request\UpdateCustomerPaymentProfileRequest::REQUEST_TYPE, $options);
        $this->checkResponse($response);

        return true;
    }

    /**
     * @param CustomerPaymentProfile $paymentProfile
     * @return array
     */
    public function getCustomerPaymentProfile(CustomerPaymentProfile $paymentProfile)
    {
        $options = array_merge(
            $this->buildAuthOptions(),
            $this->buildCustomerProfileIdOptions($paymentProfile->getCustomerProfile()),
            $this->buildCustomerPaymentProfileIdOptions($paymentProfile)
        );

        $response = $this->sendRequest(Request\GetCustomerPaymentProfileRequest::REQUEST_TYPE, $options);
        if (!$response->isSuccessful()) {
            throw new CustomerPaymentProfileNotFoundException($response->getMessage());
        }
        $responseData = $response->getData();

        return $responseData['payment_profile'];
    }

    /**
     * @param CustomerProfile $customerProfile
     * @return array
     */
    public function getCustomerProfile(CustomerProfile $customerProfile)
    {
        $options = array_merge(
            $this->buildAuthOptions(),
            $this->buildCustomerProfileIdOptions($customerProfile)
        );

        $response = $this->sendRequest(Request\GetCustomerProfileRequest::REQUEST_TYPE, $options);
        $responseData = $response->getData();

        return $responseData['profile'] ?? [];
    }

    /**
     * @param array $addressData
     * @return PaymentProfileAddressDTO
     */
    public function getPaymentProfileAddressDTO(array $addressData)
    {
        $addressDTO = new PaymentProfileAddressDTO();

        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $optionKeys = [
            AddressOption\FirstName::FIRST_NAME,
            AddressOption\LastName::LAST_NAME,
            AddressOption\Company::COMPANY,
            AddressOption\City::CITY,
            AddressOption\Zip::ZIP,
            AddressOption\PhoneNumber::PHONE_NUMBER,
            AddressOption\FaxNumber::FAX_NUMBER,
        ];

        $addressOptionKey = AddressOption\Address::ADDRESS;
        $countryOptionKey = AddressOption\Country::COUNTRY;
        $stateOptionKey = AddressOption\State::STATE;

        foreach ($optionKeys as $optionKey) {
            if (array_key_exists($optionKey, $addressData)) {
                $propertyAccessor->setValue($addressDTO, $optionKey, $addressData[$optionKey]);
            }
        }

        if (array_key_exists($addressOptionKey, $addressData)) {
            $addressDTO->setStreet($addressData[$addressOptionKey]);
        }

        /** @var CountryRepository $countryRepository */
        $countryRepository = $this->doctrineHelper->getEntityRepository(Country::class);
        /** @var RegionRepository $regionRepository */
        $regionRepository= $this->doctrineHelper->getEntityRepository(Region::class);

        if (array_key_exists($countryOptionKey, $addressData)) {
            /** @var Country $country */
            $country = $countryRepository->findOneBy(['iso3Code' => $addressData[$countryOptionKey]]);
            if ($country) {
                $addressDTO->setCountry($country);
            }
        }

        if (array_key_exists($stateOptionKey, $addressData)) {
            //region code is returned for USA only
            if ($addressDTO->getCountryCode() === 'USA') {
                /** @var Region $region */
                $region = $regionRepository->findOneBy([
                    'code' => $addressData[$stateOptionKey],
                    'country' => $addressDTO->getCountry()
                ]);
            } else {
                $region = $regionRepository->findOneBy([
                    'name' => $addressData[$stateOptionKey],
                    'country' => $addressDTO->getCountry()
                ]);
            }
            if ($region) {
                $addressDTO->setRegion($region);
            }
        }

        return $addressDTO;
    }

    /**
     * @param array $paymentData
     * @return PaymentProfileMaskedDataDTO
     */
    public function getPaymentProfileMaskedDataDTO(array $paymentData)
    {
        $maskedDataDTO = new PaymentProfileMaskedDataDTO();
        $bankAccountData = $paymentData['bank_account'] ?? null;

        if ($bankAccountData) {
            $maskedDataDTO->setAccountNumber($bankAccountData[Option\AccountNumber::ACCOUNT_NUMBER]);
            $maskedDataDTO->setRoutingNumber($bankAccountData[Option\RoutingNumber::ROUTING_NUMBER]);
            $maskedDataDTO->setNameOnAccount($bankAccountData[Option\NameOnAccount::NAME_ON_ACCOUNT]);
            $maskedDataDTO->setAccountType($bankAccountData[Option\AccountType::ACCOUNT_TYPE]);
            $maskedDataDTO->setBankName($bankAccountData[Option\BankName::BANK_NAME] ?? null);
        }

        return $maskedDataDTO;
    }

    /**
     * @param CustomerPaymentProfile $paymentProfile
     * @return bool
     */
    public function deleteCustomerPaymentProfile(CustomerPaymentProfile $paymentProfile)
    {
        $options = array_merge(
            $this->buildAuthOptions(),
            $this->buildCustomerProfileIdOptions($paymentProfile->getCustomerProfile()),
            $this->buildCustomerPaymentProfileIdOptions($paymentProfile)
        );

        $response = $this->sendRequest(Request\DeleteCustomerPaymentProfileRequest::REQUEST_TYPE, $options);
        $this->checkResponse($response);

        return true;
    }

    /**
     * @param CustomerProfile $customerProfile
     * @return bool
     */
    public function deleteCustomerProfile(CustomerProfile $customerProfile)
    {
        $options = array_merge(
            $this->buildAuthOptions(),
            $this->buildCustomerProfileIdOptions($customerProfile)
        );

        $response = $this->sendRequest(Request\DeleteCustomerProfileRequest::REQUEST_TYPE, $options);
        $this->checkResponse($response);

        return true;
    }

    /**
     * @param ResponseInterface $response
     * @return bool
     */
    protected function checkResponse(ResponseInterface $response)
    {
        if (!$response->isSuccessful()) {
            throw new \LogicException($response->getMessage());
        }

        return true;
    }

    /**
     * @return array
     */
    private function buildAuthOptions()
    {
        $config = $this->getConfig();

        return [
            Option\ApiLoginId::API_LOGIN_ID => $config->getApiLoginId(),
            Option\TransactionKey::TRANSACTION_KEY => $config->getTransactionKey()
        ];
    }

    /**
     * @param PaymentProfileAddressDTO $address
     * @return array
     */
    private function buildAddressOptions(PaymentProfileAddressDTO $address)
    {
        return [
            AddressOption\FirstName::FIRST_NAME => (string) $address->getFirstName(),
            AddressOption\LastName::LAST_NAME => (string) $address->getLastName(),
            AddressOption\Company::COMPANY => (string) $address->getCompany(),
            AddressOption\Address::ADDRESS => (string) $address->getStreet(),
            AddressOption\Country::COUNTRY => (string) $address->getCountryCode(),
            AddressOption\State::STATE => (string) $address->getRegionString(),
            AddressOption\City::CITY => (string) $address->getCity(),
            AddressOption\Zip::ZIP => (string) $address->getZip(),
            AddressOption\PhoneNumber::PHONE_NUMBER => (string) $address->getPhoneNumber(),
            AddressOption\FaxNumber::FAX_NUMBER => (string) $address->getFaxNumber()
        ];
    }

    /**
     * @param CustomerProfile $customerProfile
     * @return array
     */
    private function buildCustomerProfileIdOptions(CustomerProfile $customerProfile)
    {
        return [
            Option\CustomerProfileId::CUSTOMER_PROFILE_ID => $customerProfile->getCustomerProfileId()
        ];
    }

    /**
     * @param CustomerPaymentProfile $paymentProfile
     * @return array
     */
    private function buildCustomerPaymentProfileIdOptions(CustomerPaymentProfile $paymentProfile)
    {
        return [
            Option\CustomerPaymentProfileId::CUSTOMER_PAYMENT_PROFILE_ID =>
                $paymentProfile->getCustomerPaymentProfileId()
        ];
    }

    /**
     * @param CustomerPaymentProfile $paymentProfile
     * @return array
     */
    private function buildIsDefaultOptions(CustomerPaymentProfile $paymentProfile)
    {
        return [
            Option\IsDefault::IS_DEFAULT => $paymentProfile->isDefault()
        ];
    }

    /**
     * @return array
     */
    private function buildValidationModeOptions()
    {
        $validationMode = $this->getConfig()->isTestMode()
            ? Option\ValidationMode::TEST_MODE
            : Option\ValidationMode::LIVE_MODE;

        return [
            Option\ValidationMode::VALIDATION_MODE => $validationMode
        ];
    }

    /**
     * @param PaymentProfileEncodedDataDTO $encodedData
     * @return array
     */
    private function buildEncodedDataOptions(PaymentProfileEncodedDataDTO $encodedData)
    {
        return [
            Option\DataDescriptor::DATA_DESCRIPTOR => $encodedData->getDescriptor(),
            Option\DataValue::DATA_VALUE => $encodedData->getValue()
        ];
    }

    /**
     * @param string $type
     * @param array $options
     * @return ResponseInterface
     */
    private function sendRequest(string $type, array $options)
    {
        $this->gateway->setTestMode($this->getConfig()->isTestMode());

        return $this->gateway->request($type, $options);
    }

    /**
     * @return AuthorizeNetConfigInterface
     */
    private function getConfig()
    {
        if (!$this->config) {
            $this->config = $this->configProvider->getConfig();
        }

        return $this->config;
    }
}
