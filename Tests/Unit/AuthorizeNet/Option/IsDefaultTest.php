<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Option;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

class IsDefaultTest extends AbstractOptionTest
{
    /**
     * {@inheritDoc}
     */
    protected function getOptions(): array
    {
        return [new Option\IsDefault()];
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
                    'The required option "is_default" is missing.',
                ],
            ],
            'invalid_value' => [
                ['is_default' => 0],
                [],
                [
                    InvalidOptionsException::class,
                    'The option "is_default" with value 0 is expected to be of type "bool", but is of type "int".',
                ],
            ],
            'valid' => [
                ['is_default' => true],
                ['is_default' => true],
            ]

        ];
    }
}
