<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Option\Address;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option\Address as AddressOption;
use Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Option\AbstractOptionTest;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

class FaxNumberTest extends AbstractOptionTest
{
    /**
     * {@inheritDoc}
     */
    protected function getOptions(): array
    {
        return [new AddressOption\FaxNumber()];
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
                    'The required option "fax_number" is missing.',
                ],
            ],
            'wrong_type' => [
                ['fax_number' => 12345],
                [],
                [
                    InvalidOptionsException::class,
                    'The option "fax_number" with value 12345 is expected to be of type "string", but is of type '.
                    '"int".',
                ],
            ],
            'valid' => [
                ['fax_number' => '123456'],
                ['fax_number' => '123456'],
            ],
        ];
    }
}
