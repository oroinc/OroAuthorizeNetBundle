<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Option;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;

/**
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class PaymentDataTest extends AbstractOptionTest
{
    /**
     * {@inheritDoc}
     */
    protected function getOptions(): array
    {
        return [new Option\PaymentData()];
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptionDataProvider(): array
    {
        return [
            'required' => [
                [],
                [],
                [
                    MissingOptionsException::class,
                    'The required options "card_number", "expiration_date", "profile_type", '.
                    '"update_payment_data" are missing.',
                ],
            ],
            'not_existing_option' => [
                ['not_existing_option' => 'some value'],
                [],
                [
                    UndefinedOptionsException::class,
                    'The option "not_existing_option" does not exist. Defined options are: '.
                    '"card_number", "expiration_date", "profile_type", "update_payment_data".'
                ],
            ],
            'valid update true (creditcard)' => [
                [
                    'update_payment_data' => true,
                    'profile_type' => Option\ProfileType::CREDITCARD_TYPE,
                    'data_descriptor' => 'data descriptor',
                    'data_value' => 'data value'
                ],
                [
                    'update_payment_data' => true,
                    'profile_type' => Option\ProfileType::CREDITCARD_TYPE,
                    'data_descriptor' => 'data descriptor',
                    'data_value' => 'data value'
                ]
            ],
            'valid update true (echeck)' => [
                [
                    'update_payment_data' => true,
                    'profile_type' => Option\ProfileType::ECHECK_TYPE,
                    'data_descriptor' => 'data descriptor',
                    'data_value' => 'data value'
                ],
                [
                    'update_payment_data' => true,
                    'profile_type' => Option\ProfileType::ECHECK_TYPE,
                    'data_descriptor' => 'data descriptor',
                    'data_value' => 'data value'
                ]
            ],
            'invalid update true' => [
                [
                    'update_payment_data' => true,
                    'profile_type' => Option\ProfileType::CREDITCARD_TYPE,
                    'data_descriptor' => 'data descriptor',
                ],
                [],
                [
                    MissingOptionsException::class,
                    'The required option "data_value" is missing.'
                ]
            ],
            'valid update false' => [
                [
                    'update_payment_data' => false,
                    'profile_type' => Option\ProfileType::CREDITCARD_TYPE,
                    'card_number' => 'XXXX1234',
                    'expiration_date' => 'XXXX'
                ],
                [
                    'update_payment_data' => false,
                    'profile_type' => Option\ProfileType::CREDITCARD_TYPE,
                    'card_number' => 'XXXX1234',
                    'expiration_date' => 'XXXX'
                ]
            ],
            'invalid no update flag' => [
                [
                    'profile_type' => Option\ProfileType::CREDITCARD_TYPE,
                    'card_number' => 'XXXX1234',
                    'expiration_date' => 'XXXX'
                ],
                [],
                [
                    MissingOptionsException::class,
                    'The required option "update_payment_data" is missing.'
                ]
            ],
            'invalid required for echeck' => [
                [
                    'update_payment_data' => false,
                    'profile_type' => Option\ProfileType::ECHECK_TYPE,
                    'card_number' => 'XXXX1234',
                    'expiration_date' => 'XXXX'
                ],
                [],
                [
                    UndefinedOptionsException::class,
                    'The options "card_number", "expiration_date" do not exist. ' .
                    'Defined options are: "account_number", "account_type", "bank_name", '.
                    '"name_on_account", "profile_type",'.
                    ' "routing_number", "update_payment_data".'
                ]
            ],
            'invalid missing options for echeck' => [
                [
                    'update_payment_data' => false,
                    'profile_type' => Option\ProfileType::ECHECK_TYPE,
                    'account_number' => 'XXXX1234',
                    'routing_number' => 'XXXX4321',
                    'account_type' => 'account type',
                    'bank_name' => 'bank_name'
                ],
                [],
                [
                    MissingOptionsException::class,
                    'The required option "name_on_account" is missing.'
                ]
            ]
        ];
    }
}
