<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Option;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

class ExpirationDateTest extends AbstractOptionTest
{
    /**
     * {@inheritDoc}
     */
    protected function getOptions(): array
    {
        return [new Option\ExpirationDate()];
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
                    'The required option "expiration_date" is missing.',
                ],
            ],
            'wrong_type' => [
                ['expiration_date' => 12345],
                [],
                [
                    InvalidOptionsException::class,
                    'The option "expiration_date" with value 12345 is expected to be of type "string", but is of '.
                    'type "int".',
                ],
            ],
            'valid' => [
                ['expiration_date' => 'XXXX'],
                ['expiration_date' => 'XXXX'],
            ],
        ];
    }
}
