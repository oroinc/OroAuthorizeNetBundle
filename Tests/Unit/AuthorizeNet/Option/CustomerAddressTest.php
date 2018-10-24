<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Option;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

class CustomerAddressTest extends AbstractOptionTest
{
    /** {@inheritdoc} */
    protected function getOptions()
    {
        return [new Option\CustomerAddress()];
    }

    /** {@inheritdoc} */
    public function configureOptionDataProvider()
    {
        return [
            'required' => [
                [],
                [],
                [
                    'Symfony\Component\OptionsResolver\Exception\MissingOptionsException',
                    'The required options "address", "city", "company", "country", "first_name", '.
                    '"last_name", "state", "zip" are missing.',
                ],
            ],
            'not_existing_option' => [
                ['not_existing_option' => 'some value'],
                [],
                [
                    'Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException',
                    'The option "not_existing_option" does not exist. Defined options are: "address", "city", '.
                    '"company", "country", "fax_number", "first_name", "last_name", "phone_number", "state", "zip".',
                ],
            ],
            'valid' => [
                [
                    'first_name' => 'first name',
                    'last_name' => 'last name',
                    'company' => 'company name',
                    'address' => 'street address',
                    'city' => 'city name',
                    'state' => 'state name',
                    'zip' => '123456',
                    'country' => 'USA',
                    'phone_number' => '+123456789',
                    'fax_number' => '+123456789'
                ],
                [
                    'first_name' => 'first name',
                    'last_name' => 'last name',
                    'company' => 'company name',
                    'address' => 'street address',
                    'city' => 'city name',
                    'state' => 'state name',
                    'zip' => '123456',
                    'country' => 'USA',
                    'phone_number' => '+123456789',
                    'fax_number' => '+123456789'
                ]
            ],
        ];
    }
}
