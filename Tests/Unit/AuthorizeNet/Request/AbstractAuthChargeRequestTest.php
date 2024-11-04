<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Request;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Request\AuthorizeRequest;

abstract class AbstractAuthChargeRequestTest extends AbstractRequestTest
{
    #[\Override]
    public function optionsProvider(): array
    {
        $default = [
            Option\Transaction::TRANSACTION_TYPE => (new AuthorizeRequest())->getType(),
            Option\Amount::AMOUNT => 10.00,
            Option\Currency::CURRENCY => Option\Currency::US_DOLLAR,
        ];
        $chargeCreditCard = [
            Option\DataDescriptor::DATA_DESCRIPTOR => 'some_data_descriptor',
            Option\DataValue::DATA_VALUE => 'some_data_value',
            Option\ChargeType::NAME => Option\ChargeType::TYPE_CREDIT_CARD,
        ];

        $addressOptions = [
            // Address options required
            Option\Address\FirstName::FIRST_NAME => 'first name',
            Option\Address\LastName::LAST_NAME => 'last name',
            Option\Address\Company::COMPANY => '',
            Option\Address\Address::ADDRESS => 'street address',
            Option\Address\City::CITY => 'city name',
            Option\Address\State::STATE => 'state name',
            Option\Address\Zip::ZIP => 'zip',
            Option\Address\Country::COUNTRY => 'country name',
            Option\Address\PhoneNumber::PHONE_NUMBER => '+123456'
        ];

        $chargeCustomerProfile = [
            Option\CustomerProfileId::CUSTOMER_PROFILE_ID => "777",
            Option\CustomerPaymentProfileId::CUSTOMER_PAYMENT_PROFILE_ID => "888",
            Option\ChargeType::NAME => Option\ChargeType::TYPE_PAYMENT_PROFILE
        ];

        return [
            'defaultChargeCreditCard' => [
                array_merge($default, $chargeCreditCard, $addressOptions)
            ],
            'chargeCustomerProfile' => [
                array_merge($default, $chargeCustomerProfile)
            ],
            'chargeCreditCardWithCreateProfileTrue' => [
                array_merge(
                    $default,
                    $chargeCreditCard,
                    $addressOptions,
                    [
                        Option\CreateProfile::NAME => true,
                        Option\CustomerProfileId::CUSTOMER_PROFILE_ID => '12345'
                    ]
                )
            ],
            'chargeCreditCardWithCreateProfileAndCustomerDataId' => [
                array_merge(
                    $default,
                    $chargeCreditCard,
                    $addressOptions,
                    [
                        Option\CreateProfile::NAME => true,
                        Option\CustomerDataId::NAME => 'oro-x-y'
                    ]
                )
            ],
            'chargeCreditCardWithCreateProfileFalse' => [
                array_merge(
                    $default,
                    $chargeCreditCard,
                    $addressOptions,
                    [
                        Option\CreateProfile::NAME => false
                    ]
                )
            ]
        ];
    }
}
