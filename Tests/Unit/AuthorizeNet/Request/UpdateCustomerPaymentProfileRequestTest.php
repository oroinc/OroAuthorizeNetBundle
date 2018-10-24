<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Request;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option\Address as AddressOption;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Request\UpdateCustomerPaymentProfileRequest;

class UpdateCustomerPaymentProfileRequestTest extends AbstractRequestTest
{
    protected function setUp()
    {
        $this->request = new UpdateCustomerPaymentProfileRequest();
    }

    /**
     * {@inheritdoc}
     */
    public function optionsProvider()
    {
        return [
            'update credit card' => [
                [
                    Option\CustomerProfileId::CUSTOMER_PROFILE_ID => '12345',
                    Option\CustomerPaymentProfileId::CUSTOMER_PAYMENT_PROFILE_ID => '12345',
                    AddressOption\FirstName::FIRST_NAME => 'first name',
                    AddressOption\LastName::LAST_NAME => 'last name',
                    AddressOption\Company::COMPANY => 'company name',
                    AddressOption\Address::ADDRESS => 'street address',
                    AddressOption\City::CITY => 'city name',
                    AddressOption\State::STATE => 'state name',
                    AddressOption\Zip::ZIP => 'zip',
                    AddressOption\Country::COUNTRY => 'country name',
                    AddressOption\PhoneNumber::PHONE_NUMBER => '+123456',
                    Option\UpdatePaymentData::UPDATE_PAYMENT_DATA => true,
                    Option\ProfileType::PROFILE_TYPE => Option\ProfileType::CREDITCARD_TYPE,
                    Option\DataDescriptor::DATA_DESCRIPTOR => 'some_data_descriptor',
                    Option\DataValue::DATA_VALUE => 'some_data_value',
                    Option\IsDefault::IS_DEFAULT => true,
                    Option\ValidationMode::VALIDATION_MODE => Option\ValidationMode::TEST_MODE
                ]
            ],
            'no update credit card' => [
                [
                    Option\CustomerProfileId::CUSTOMER_PROFILE_ID => '12345',
                    Option\CustomerPaymentProfileId::CUSTOMER_PAYMENT_PROFILE_ID => '12345',
                    AddressOption\FirstName::FIRST_NAME => 'first name',
                    AddressOption\LastName::LAST_NAME => 'last name',
                    AddressOption\Company::COMPANY => 'company name',
                    AddressOption\Address::ADDRESS => 'street address',
                    AddressOption\City::CITY => 'city name',
                    AddressOption\State::STATE => 'state name',
                    AddressOption\Zip::ZIP => 'zip',
                    AddressOption\Country::COUNTRY => 'country name',
                    AddressOption\PhoneNumber::PHONE_NUMBER => '+123456',
                    Option\UpdatePaymentData::UPDATE_PAYMENT_DATA => false,
                    Option\ProfileType::PROFILE_TYPE => Option\ProfileType::CREDITCARD_TYPE,
                    Option\CardNumber::CARD_NUMBER => 'XXXX1234',
                    Option\ExpirationDate::EXPIRATION_DATE => 'XXXX',
                    Option\IsDefault::IS_DEFAULT => true,
                    Option\ValidationMode::VALIDATION_MODE => Option\ValidationMode::TEST_MODE
                ]
            ],
            'update bank account' => [
                [
                    Option\CustomerProfileId::CUSTOMER_PROFILE_ID => '12345',
                    Option\CustomerPaymentProfileId::CUSTOMER_PAYMENT_PROFILE_ID => '12345',
                    AddressOption\FirstName::FIRST_NAME => 'first name',
                    AddressOption\LastName::LAST_NAME => 'last name',
                    AddressOption\Company::COMPANY => 'company name',
                    AddressOption\Address::ADDRESS => 'street address',
                    AddressOption\City::CITY => 'city name',
                    AddressOption\State::STATE => 'state name',
                    AddressOption\Zip::ZIP => 'zip',
                    AddressOption\Country::COUNTRY => 'country name',
                    AddressOption\PhoneNumber::PHONE_NUMBER => '+123456',
                    Option\UpdatePaymentData::UPDATE_PAYMENT_DATA => true,
                    Option\ProfileType::PROFILE_TYPE => Option\ProfileType::ECHECK_TYPE,
                    Option\DataDescriptor::DATA_DESCRIPTOR => 'some_data_descriptor',
                    Option\DataValue::DATA_VALUE => 'some_data_value',
                    Option\IsDefault::IS_DEFAULT => true,
                    Option\ValidationMode::VALIDATION_MODE => Option\ValidationMode::TEST_MODE
                ]
            ],
            'no update bank account' => [
                [
                    Option\CustomerProfileId::CUSTOMER_PROFILE_ID => '12345',
                    Option\CustomerPaymentProfileId::CUSTOMER_PAYMENT_PROFILE_ID => '12345',
                    AddressOption\FirstName::FIRST_NAME => 'first name',
                    AddressOption\LastName::LAST_NAME => 'last name',
                    AddressOption\Company::COMPANY => 'company name',
                    AddressOption\Address::ADDRESS => 'street address',
                    AddressOption\City::CITY => 'city name',
                    AddressOption\State::STATE => 'state name',
                    AddressOption\Zip::ZIP => 'zip',
                    AddressOption\Country::COUNTRY => 'country name',
                    AddressOption\PhoneNumber::PHONE_NUMBER => '+123456',
                    Option\UpdatePaymentData::UPDATE_PAYMENT_DATA => false,
                    Option\ProfileType::PROFILE_TYPE => Option\ProfileType::ECHECK_TYPE,
                    Option\AccountNumber::ACCOUNT_NUMBER => 'XXXX1234',
                    Option\RoutingNumber::ROUTING_NUMBER => 'XXXX4321',
                    Option\NameOnAccount::NAME_ON_ACCOUNT => 'name',
                    Option\AccountType::ACCOUNT_TYPE => 'account type',
                    Option\BankName::BANK_NAME => 'bank name',
                    Option\IsDefault::IS_DEFAULT => true,
                    Option\ValidationMode::VALIDATION_MODE => Option\ValidationMode::TEST_MODE
                ]
            ]
        ];
    }
}
