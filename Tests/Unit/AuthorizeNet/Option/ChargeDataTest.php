<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Option;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

class ChargeDataTest extends AbstractOptionTest
{
    #[\Override]
    protected function getOptions(): array
    {
        return [new Option\ChargeData()];
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    #[\Override]
    public function configureOptionDataProvider(): array
    {
        $profileOptions = [
            Option\CreateProfile::NAME => true,
            Option\CustomerProfileId::CUSTOMER_PROFILE_ID => '12345'
        ];

        $addressOptions = [
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

        return [
            'requiredChargeType' => [
                array_merge(
                    $profileOptions,
                    $addressOptions
                ),
                [],
                [
                    MissingOptionsException::class,
                    sprintf('The required option "%s" is missing.', Option\ChargeType::NAME)
                ]
            ],
            'requiredForChargeCreditCard' => [
                array_merge(
                    $profileOptions,
                    $addressOptions,
                    [Option\ChargeType::NAME => Option\ChargeType::TYPE_CREDIT_CARD]
                ),
                [],
                [
                    MissingOptionsException::class,
                    sprintf(
                        'The required options "%s", "%s" are missing.',
                        Option\DataDescriptor::DATA_DESCRIPTOR,
                        Option\DataValue::DATA_VALUE
                    )
                ]
            ],
            'requiredForChargeCustomerProfile' => [
                [Option\ChargeType::NAME => Option\ChargeType::TYPE_PAYMENT_PROFILE],
                [],
                [
                    MissingOptionsException::class,
                    sprintf(
                        'The required options "%s", "%s" are missing.',
                        Option\CustomerPaymentProfileId::CUSTOMER_PAYMENT_PROFILE_ID,
                        Option\CustomerProfileId::CUSTOMER_PROFILE_ID
                    )
                ]
            ],
            'validChargeCreditCard' => [
                array_merge(
                    $profileOptions,
                    $addressOptions,
                    [
                        Option\ChargeType::NAME => Option\ChargeType::TYPE_CREDIT_CARD,
                        Option\DataDescriptor::DATA_DESCRIPTOR => 'data descriptor',
                        Option\DataValue::DATA_VALUE => 'data value'
                    ]
                ),
                array_merge(
                    $profileOptions,
                    $addressOptions,
                    [
                        Option\ChargeType::NAME => Option\ChargeType::TYPE_CREDIT_CARD,
                        Option\DataDescriptor::DATA_DESCRIPTOR => 'data descriptor',
                        Option\DataValue::DATA_VALUE => 'data value'
                    ]
                )
            ],
            'validChargeCustomerProfileNoCardCode' => [
                array_merge(
                    $profileOptions,
                    [
                        Option\ChargeType::NAME => Option\ChargeType::TYPE_PAYMENT_PROFILE,
                        Option\CustomerPaymentProfileId::CUSTOMER_PAYMENT_PROFILE_ID => '777',
                        Option\CustomerProfileId::CUSTOMER_PROFILE_ID => '888'
                    ]
                ),
                array_merge(
                    $profileOptions,
                    [
                        Option\ChargeType::NAME => Option\ChargeType::TYPE_PAYMENT_PROFILE,
                        Option\CustomerPaymentProfileId::CUSTOMER_PAYMENT_PROFILE_ID => '777',
                        Option\CustomerProfileId::CUSTOMER_PROFILE_ID => '888'
                    ]
                )
            ],
            'validChargeCustomerProfileHasCardCode' => [
                array_merge(
                    $profileOptions,
                    [
                        Option\ChargeType::NAME => Option\ChargeType::TYPE_PAYMENT_PROFILE,
                        Option\CustomerPaymentProfileId::CUSTOMER_PAYMENT_PROFILE_ID => '777',
                        Option\CustomerProfileId::CUSTOMER_PROFILE_ID => '888',
                        Option\CardCode::NAME => '123'
                    ]
                ),
                array_merge(
                    $profileOptions,
                    [
                        Option\ChargeType::NAME => Option\ChargeType::TYPE_PAYMENT_PROFILE,
                        Option\CustomerPaymentProfileId::CUSTOMER_PAYMENT_PROFILE_ID => '777',
                        Option\CustomerProfileId::CUSTOMER_PROFILE_ID => '888',
                        Option\CardCode::NAME => '123'
                    ]
                )
            ]
        ];
    }
}
