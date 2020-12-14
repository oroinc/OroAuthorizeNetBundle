<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Client\RequestConfigurator;

use net\authorize\api\contract\v1 as AnetAPI;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Client\RequestConfigurator as RequestConfigurator;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option\Address as AddressOption;

class CreateCustomerPaymentProfileRequestConfiguratorTest extends AbstractRequestConfiguratorTest
{
    protected function getConfigurator()
    {
        return new RequestConfigurator\CreateCustomerPaymentProfileRequestConfigurator();
    }

    public function isApplicableProvider()
    {
        return [
            'supported' => [
                'request' => new AnetAPI\CreateCustomerPaymentProfileRequest(),
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
                'request' => new AnetAPI\CreateCustomerPaymentProfileRequest(),
                'options' => [
                    Option\CustomerProfileId::CUSTOMER_PROFILE_ID => '123',
                    Option\ValidationMode::VALIDATION_MODE => 'testMode',
                    Option\DataDescriptor::DATA_DESCRIPTOR => 'data desc',
                    Option\DataValue::DATA_VALUE => 'data value',
                    Option\IsDefault::IS_DEFAULT => true,
                    AddressOption\FirstName::FIRST_NAME => 'first',
                    AddressOption\LastName::LAST_NAME => 'last',
                    AddressOption\Company::COMPANY => 'company',
                    AddressOption\City::CITY => 'city',
                    AddressOption\Address::ADDRESS => 'street address',
                    AddressOption\State::STATE => 'state',
                    AddressOption\Country::COUNTRY => 'US',
                    AddressOption\Zip::ZIP => '12345',
                    AddressOption\PhoneNumber::PHONE_NUMBER => '+123456789',
                    AddressOption\FaxNumber::FAX_NUMBER => '+123456789'
                ],
                'expectedRequest' => (new AnetAPI\CreateCustomerPaymentProfileRequest())
                    ->setCustomerProfileId('123')
                    ->setValidationMode('testMode')
                    ->setPaymentProfile(
                        (new AnetAPI\CustomerPaymentProfileType())
                            ->setPayment((new AnetAPI\PaymentType())
                                ->setOpaqueData((new AnetAPI\OpaqueDataType())
                                    ->setDataDescriptor('data desc')
                                    ->setDataValue('data value')))
                            ->setDefaultPaymentProfile(true)
                            ->setBillTo((new AnetAPI\CustomerAddressType())
                                ->setPhoneNumber('+123456789')
                                ->setFaxNumber('+123456789')
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
                'request' => new AnetAPI\CreateCustomerPaymentProfileRequest(),
                'options' => [
                    Option\CustomerProfileId::CUSTOMER_PROFILE_ID => '123',
                    Option\ValidationMode::VALIDATION_MODE => 'testMode',
                    Option\DataDescriptor::DATA_DESCRIPTOR => 'data desc',
                    Option\DataValue::DATA_VALUE => 'data value',
                    Option\IsDefault::IS_DEFAULT => true
                ],
                'expectedRequest' => (new AnetAPI\CreateCustomerPaymentProfileRequest())
                    ->setCustomerProfileId('123')
                    ->setValidationMode('testMode')
                    ->setPaymentProfile(
                        (new AnetAPI\CustomerPaymentProfileType())
                            ->setPayment((new AnetAPI\PaymentType())
                                ->setOpaqueData((new AnetAPI\OpaqueDataType())
                                    ->setDataDescriptor('data desc')
                                    ->setDataValue('data value')))
                            ->setDefaultPaymentProfile(true)
                            ->setBillTo((new AnetAPI\CustomerAddressType()))
                    )
            ]
        ];
    }
}
