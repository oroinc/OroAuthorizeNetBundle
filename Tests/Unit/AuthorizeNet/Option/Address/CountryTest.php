<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Option\Address;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option\Address as AddressOption;
use Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Option\AbstractOptionTest;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

class CountryTest extends AbstractOptionTest
{
    #[\Override]
    protected function getOptions(): array
    {
        return [new AddressOption\Country()];
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
                    'The required option "country" is missing.',
                ],
            ],
            'wrong_type' => [
                ['country' => 12345],
                [],
                [
                    InvalidOptionsException::class,
                    'The option "country" with value 12345 is expected to be of type "string", but is of type ' .
                    '"int".',
                ],
            ],
            'valid' => [
                ['country' => 'country name'],
                ['country' => 'country name'],
            ],
        ];
    }
}
