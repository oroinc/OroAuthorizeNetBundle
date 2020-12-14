<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Client\RequestConfigurator;

use net\authorize\api\contract\v1 as AnetAPI;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Client\RequestConfigurator as RequestConfigurator;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option\Address as AddressOption;

/**
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class UpdateCustomerPaymentProfileRequestConfiguratorTest extends AbstractRequestConfiguratorTest
{
    protected function getConfigurator()
    {
        return new RequestConfigurator\UpdateCustomerPaymentProfileRequestConfigurator();
    }

    public function isApplicableProvider()
    {
        return [
            'supported' => [
                'request' => new AnetAPI\UpdateCustomerPaymentProfileRequest(),
                'options' => [],
                'expectedResult' => true
            ],
            'unsupported' => [
                'request' => new AnetAPI\ANetApiRequestType(),
                'options' => [],
                'expectedResult' => false
            ]
        ];
    }

    /**
     * @return array
     */
    public function handleProvider()
    {
        return [
            'all options' => [
                'request' => new AnetAPI\UpdateCustomerPaymentProfileRequest(),
                'options' => [
                    Option\CustomerProfileId::CUSTOMER_PROFILE_ID => '123',
                    Option\CustomerPaymentProfileId::CUSTOMER_PAYMENT_PROFILE_ID => '321',
                    Option\ValidationMode::VALIDATION_MODE => 'testMode',
                    Option\UpdatePaymentData::UPDATE_PAYMENT_DATA => false,
                    Option\CardNumber::CARD_NUMBER => 'XXXX1234',
                    Option\ExpirationDate::EXPIRATION_DATE => 'XXXX',
                    Option\IsDefault::IS_DEFAULT => true,
                    AddressOption\FirstName::FIRST_NAME => 'first',
                    AddressOption\LastName::LAST_NAME => 'last',
                    AddressOption\Company::COMPANY => 'company',
                    AddressOption\City::CITY => 'city',
                    AddressOption\Address::ADDRESS => 'street address',
                    AddressOption\State::STATE => 'state',
                    AddressOption\Country::COUNTRY => 'US',
                    AddressOption\Zip::ZIP => '12345',
                    AddressOption\PhoneNumber::PHONE_NUMBER => '+123456789'
                ],
                'expectedRequest' => (new AnetAPI\UpdateCustomerPaymentProfileRequest())
                    ->setCustomerProfileId('123')
                    ->setValidationMode('testMode')
                    ->setPaymentProfile(
                        (new AnetAPI\CustomerPaymentProfileExType())
                            ->setCustomerPaymentProfileId('321')
                            ->setPayment((new AnetAPI\PaymentType())
                                ->setCreditCard((new AnetAPI\CreditCardType())
                                    ->setCardNumber('XXXX1234')
                                    ->setExpirationDate('XXXX')))
                            ->setDefaultPaymentProfile(true)
                            ->setBillTo((new AnetAPI\CustomerAddressType())
                                ->setPhoneNumber('+123456789')
                                ->setFirstName('first')
                                ->setLastName('last')
                                ->setCompany('company')
                                ->setCity('city')
                                ->setAddress('street address')
                                ->setState('state')
                                ->setCountry('US')
                                ->setZip('12345'))
                    )
            ],
            'no billTo (no address)' => [
                'request' => new AnetAPI\UpdateCustomerPaymentProfileRequest(),
                'options' => [
                    Option\CustomerProfileId::CUSTOMER_PROFILE_ID => '123',
                    Option\ValidationMode::VALIDATION_MODE => 'testMode',
                    Option\UpdatePaymentData::UPDATE_PAYMENT_DATA => true,
                    Option\ProfileType::PROFILE_TYPE => Option\ProfileType::CREDITCARD_TYPE,
                    Option\DataDescriptor::DATA_DESCRIPTOR => 'data desc',
                    Option\DataValue::DATA_VALUE => 'data value',
                    Option\IsDefault::IS_DEFAULT => true
                ],
                'expectedRequest' => (new AnetAPI\UpdateCustomerPaymentProfileRequest())
                    ->setCustomerProfileId('123')
                    ->setValidationMode('testMode')
                    ->setPaymentProfile(
                        (new AnetAPI\CustomerPaymentProfileExType())
                            ->setPayment((new AnetAPI\PaymentType())
                                ->setOpaqueData((new AnetAPI\OpaqueDataType())
                                    ->setDataDescriptor('data desc')
                                    ->setDataValue('data value')))
                            ->setDefaultPaymentProfile(true)
                            ->setBillTo((new AnetAPI\CustomerAddressType()))
                    )
            ],
            'bank account no update payment data' => [
                'request' => new AnetAPI\UpdateCustomerPaymentProfileRequest(),
                'options' => [
                    Option\CustomerProfileId::CUSTOMER_PROFILE_ID => '123',
                    Option\ValidationMode::VALIDATION_MODE => 'testMode',
                    Option\UpdatePaymentData::UPDATE_PAYMENT_DATA => false,
                    Option\ProfileType::PROFILE_TYPE => Option\ProfileType::ECHECK_TYPE,
                    Option\AccountType::ACCOUNT_TYPE => 'account type',
                    Option\AccountNumber::ACCOUNT_NUMBER => 'XXXX1234',
                    Option\RoutingNumber::ROUTING_NUMBER => 'XXXX4321',
                    Option\NameOnAccount::NAME_ON_ACCOUNT => 'first last',
                    Option\BankName::BANK_NAME => 'bank name',
                    Option\IsDefault::IS_DEFAULT => false
                ],
                'expectedRequest' => (new AnetAPI\UpdateCustomerPaymentProfileRequest())
                    ->setCustomerProfileId('123')
                    ->setValidationMode('testMode')
                    ->setPaymentProfile(
                        (new AnetAPI\CustomerPaymentProfileExType())
                            ->setPayment((new AnetAPI\PaymentType())
                                ->setBankAccount((new AnetAPI\BankAccountType())
                                    ->setAccountType('account type')
                                    ->setAccountNumber('XXXX1234')
                                    ->setRoutingNumber('XXXX4321')
                                    ->setNameOnAccount('first last')
                                    ->setBankName('bank name')))
                            ->setDefaultPaymentProfile(false)
                            ->setBillTo(new AnetAPI\CustomerAddressType())
                    )
            ]
        ];
    }
}
