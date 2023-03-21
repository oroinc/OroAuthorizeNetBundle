<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Request;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option\Address as AddressOption;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Request\CreateCustomerPaymentProfileRequest;

class CreateCustomerPaymentProfileRequestTest extends AbstractRequestTest
{
    protected function setUp(): void
    {
        $this->request = new CreateCustomerPaymentProfileRequest();
    }

    /**
     * {@inheritDoc}
     */
    public function optionsProvider(): array
    {
        return [
            'default' => [
                [
                    Option\CustomerProfileId::CUSTOMER_PROFILE_ID => '12345',
                    AddressOption\FirstName::FIRST_NAME => 'first name',
                    AddressOption\LastName::LAST_NAME => 'last name',
                    AddressOption\Company::COMPANY => 'company name',
                    AddressOption\Address::ADDRESS => 'street address',
                    AddressOption\City::CITY => 'city name',
                    AddressOption\State::STATE => 'state name',
                    AddressOption\Zip::ZIP => 'zip',
                    AddressOption\Country::COUNTRY => 'country name',
                    AddressOption\PhoneNumber::PHONE_NUMBER => '+123456',
                    Option\DataDescriptor::DATA_DESCRIPTOR => 'some_data_descriptor',
                    Option\DataValue::DATA_VALUE => 'some_data_value',
                    Option\IsDefault::IS_DEFAULT => true,
                    Option\ValidationMode::VALIDATION_MODE => Option\ValidationMode::TEST_MODE
                ]
            ]
        ];
    }
}
