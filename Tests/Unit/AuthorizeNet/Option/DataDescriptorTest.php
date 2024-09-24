<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Option;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

class DataDescriptorTest extends AbstractOptionTest
{
    #[\Override]
    protected function getOptions(): array
    {
        return [new Option\DataDescriptor()];
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
                    'The required option "data_descriptor" is missing.',
                ],
            ],
            'wrong_type' => [
                ['data_descriptor' => 12345],
                [],
                [
                    InvalidOptionsException::class,
                    'The option "data_descriptor" with value 12345 is expected to be of type "string", but is of '.
                    'type "int".',
                ],
            ],
            'valid' => [
                ['data_descriptor' => 'some_data_descriptor'],
                ['data_descriptor' => 'some_data_descriptor'],
            ],
        ];
    }
}
