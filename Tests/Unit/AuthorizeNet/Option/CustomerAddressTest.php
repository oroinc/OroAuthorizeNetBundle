<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Option;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;

class CustomerAddressTest extends AbstractOptionTest
{
    #[\Override]
    protected function getOptions(): array
    {
        return [new Option\CustomerAddress()];
    }

    #[\Override]
    public function configureOptionDataProvider(): array
    {
        return [
            'required' => [
                [],
                [],
                [
                    MissingOptionsException::class,
                    'The required options "address", "city", "company", "country", "first_name", '.
                    '"last_name", "state", "zip" are missing.',
                ],
            ],
            'not_existing_option' => [
                ['not_existing_option' => 'some value'],
                [],
                [
                    UndefinedOptionsException::class,
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
