<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Option\Address;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option\Address as AddressOption;
use Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Option\AbstractOptionTest;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

class AddressTest extends AbstractOptionTest
{
    #[\Override]
    protected function getOptions(): array
    {
        return [new AddressOption\Address()];
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
                    'The required option "address" is missing.',
                ],
            ],
            'wrong_type' => [
                ['address' => 12345],
                [],
                [
                    InvalidOptionsException::class,
                    'The option "address" with value 12345 is expected to be of type "string", but is of type ' .
                    '"int".',
                ],
            ],
            'valid' => [
                ['address' => 'street address'],
                ['address' => 'street address'],
            ],
        ];
    }
}
