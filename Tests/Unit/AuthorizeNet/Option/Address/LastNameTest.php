<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Option\Address;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option\Address as AddressOption;
use Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Option\AbstractOptionTest;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

class LastNameTest extends AbstractOptionTest
{
    /**
     * {@inheritDoc}
     */
    protected function getOptions(): array
    {
        return [new AddressOption\LastName()];
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
                    'The required option "last_name" is missing.',
                ],
            ],
            'wrong_type' => [
                ['last_name' => 12345],
                [],
                [
                    InvalidOptionsException::class,
                    'The option "last_name" with value 12345 is expected to be of type "string", but is of type '.
                    '"int".',
                ],
            ],
            'valid' => [
                ['last_name' => 'some_name'],
                ['last_name' => 'some_name'],
            ],
        ];
    }
}
