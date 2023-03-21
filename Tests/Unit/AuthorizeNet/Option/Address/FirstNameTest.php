<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Option\Address;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option\Address as AddressOption;
use Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Option\AbstractOptionTest;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

class FirstNameTest extends AbstractOptionTest
{
    /**
     * {@inheritDoc}
     */
    protected function getOptions(): array
    {
        return [new AddressOption\FirstName()];
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
                    'The required option "first_name" is missing.',
                ],
            ],
            'wrong_type' => [
                ['first_name' => 12345],
                [],
                [
                    InvalidOptionsException::class,
                    'The option "first_name" with value 12345 is expected to be of type "string", but is of type '.
                    '"int".',
                ],
            ],
            'valid' => [
                ['first_name' => 'some_name'],
                ['first_name' => 'some_name'],
            ],
        ];
    }
}
