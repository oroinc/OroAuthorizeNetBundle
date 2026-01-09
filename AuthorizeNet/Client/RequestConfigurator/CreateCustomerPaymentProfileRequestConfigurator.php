<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Client\RequestConfigurator;

use net\authorize\api\contract\v1 as AnetAPI;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option\Address as AddressOption;
use Oro\Bundle\EntityExtendBundle\PropertyAccess;

/**
 * request configurator for createCustomerPaymentProfile request
 */
class CreateCustomerPaymentProfileRequestConfigurator implements RequestConfiguratorInterface
{
    #[\Override]
    public function isApplicable(AnetAPI\ANetApiRequestType $request, array $options)
    {
        return $request instanceof AnetAPI\CreateCustomerPaymentProfileRequest;
    }

    /**
     * @param AnetAPI\ANetApiRequestType|AnetAPI\CreateCustomerPaymentProfileRequest $request
     * @param array $options
     */
    #[\Override]
    public function handle(AnetAPI\ANetApiRequestType $request, array &$options)
    {
        if (array_key_exists(Option\CustomerProfileId::CUSTOMER_PROFILE_ID, $options)) {
            $request->setCustomerProfileId($options[Option\CustomerProfileId::CUSTOMER_PROFILE_ID]);
        }

        if (array_key_exists(Option\ValidationMode::VALIDATION_MODE, $options)) {
            $request->setValidationMode($options[Option\ValidationMode::VALIDATION_MODE]);
        }

        $request->setPaymentProfile($this->buildCustomerPaymentProfile($options));

        // Remove handled options to prevent handling in fallback configurator
        unset(
            $options[Option\DataDescriptor::DATA_DESCRIPTOR],
            $options[Option\DataValue::DATA_VALUE],
            $options[Option\CustomerProfileId::CUSTOMER_PROFILE_ID],
            $options[Option\ValidationMode::VALIDATION_MODE],
            $options[Option\IsDefault::IS_DEFAULT],
            $options[AddressOption\FirstName::FIRST_NAME],
            $options[AddressOption\LastName::LAST_NAME],
            $options[AddressOption\Company::COMPANY],
            $options[AddressOption\City::CITY],
            $options[AddressOption\Address::ADDRESS],
            $options[AddressOption\State::STATE],
            $options[AddressOption\Zip::ZIP],
            $options[AddressOption\Country::COUNTRY],
            $options[AddressOption\PhoneNumber::PHONE_NUMBER],
            $options[AddressOption\FaxNumber::FAX_NUMBER]
        );

        if (!empty($options[Option\UpdatePaymentData::UPDATE_PAYMENT_DATA])) {
            unset($options[Option\UpdatePaymentData::UPDATE_PAYMENT_DATA]);
        }
    }

    /**
     * @param array $options
     * @return AnetAPI\CustomerPaymentProfileType
     */
    protected function buildCustomerPaymentProfile(array $options)
    {
        $paymentProfile = $this->createCustomerPaymentProfile();
        $paymentProfile->setBillTo($this->getCustomerAddress($options));

        if (array_key_exists(Option\IsDefault::IS_DEFAULT, $options)) {
            $paymentProfile->setDefaultPaymentProfile($options[Option\IsDefault::IS_DEFAULT]);
        }

        if (
            array_key_exists(Option\DataDescriptor::DATA_DESCRIPTOR, $options)
            && array_key_exists(Option\DataValue::DATA_VALUE, $options)
        ) {
            $paymentProfile->setPayment($this->getPaymentType($options));
        }

        return $paymentProfile;
    }

    /**
     * @return AnetAPI\CustomerPaymentProfileType
     */
    protected function createCustomerPaymentProfile()
    {
        return new AnetAPI\CustomerPaymentProfileType();
    }

    /**
     * @param array $options
     * @return AnetAPI\CustomerAddressType
     */
    protected function getCustomerAddress(array $options)
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $customerAddress = new AnetAPI\CustomerAddressType();
        $optionKeys = [
            AddressOption\FirstName::FIRST_NAME,
            AddressOption\LastName::LAST_NAME,
            AddressOption\Company::COMPANY,
            AddressOption\Address::ADDRESS,
            AddressOption\City::CITY,
            AddressOption\State::STATE,
            AddressOption\Zip::ZIP,
            AddressOption\Country::COUNTRY,
            AddressOption\PhoneNumber::PHONE_NUMBER,
            AddressOption\FaxNumber::FAX_NUMBER
        ];

        foreach ($optionKeys as $optionKey) {
            if (array_key_exists($optionKey, $options)) {
                $propertyAccessor->setValue($customerAddress, $optionKey, $options[$optionKey]);
            }
        }

        return $customerAddress;
    }

    /**
     * @param array $options
     * @return AnetAPI\PaymentType
     */
    protected function getPaymentType(array $options)
    {
        $opaqueDataType = new AnetAPI\OpaqueDataType();
        $opaqueDataType
            ->setDataDescriptor($options[Option\DataDescriptor::DATA_DESCRIPTOR])
            ->setDataValue($options[Option\DataValue::DATA_VALUE]);

        $paymentType = new AnetAPI\PaymentType();
        $paymentType->setOpaqueData($opaqueDataType);

        return $paymentType;
    }
}
