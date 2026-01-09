<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Option\Address;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option\Address as AddressOption;
use Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Option\AbstractOptionTest;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

class PhoneNumberTest extends AbstractOptionTest
{
    #[\Override]
    protected function getOptions(): array
    {
        return [new AddressOption\PhoneNumber()];
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
                    'The required option "phone_number" is missing.',
                ],
            ],
            'wrong_type' => [
                ['phone_number' => 12345],
                [],
                [
                    InvalidOptionsException::class,
                    'The option "phone_number" with value 12345 is expected to be of type "string", but is of type ' .
                    '"int".',
                ],
            ],
            'valid' => [
                ['phone_number' => '123456'],
                ['phone_number' => '123456'],
            ],
        ];
    }
}
