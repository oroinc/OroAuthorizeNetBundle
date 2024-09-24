<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Option;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

class DataValueTest extends AbstractOptionTest
{
    #[\Override]
    protected function getOptions(): array
    {
        return [new Option\DataValue()];
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
                    'The required option "data_value" is missing.',
                ],
            ],
            'wrong_type' => [
                ['data_value' => 12345],
                [],
                [
                    InvalidOptionsException::class,
                    'The option "data_value" with value 12345 is expected to be of type "string", but is of '.
                    'type "int".',
                ],
            ],
            'valid' => [
                ['data_value' => 'some_data_value'],
                ['data_value' => 'some_data_value'],
            ],
        ];
    }
}
