<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Option;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

class CardCodeTest extends AbstractOptionTest
{
    #[\Override]
    protected function getOptions(): array
    {
        return [new Option\CardCode()];
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
                    sprintf('The required option "%s" is missing.', Option\CardCode::NAME),
                ],
            ],
            'wrong_type' => [
                [Option\CardCode::NAME => 123],
                [],
                [
                    InvalidOptionsException::class,
                    sprintf(
                        'The option "%s" with value 123 is expected to be of type "string", but is of '.
                        'type "int".',
                        Option\CardCode::NAME
                    )
                ],
            ],
            'valid' => [
                [Option\CardCode::NAME => '123'],
                [Option\CardCode::NAME => '123']
            ],
            'otherValid' => [
                [Option\CardCode::NAME => '8888'],
                [Option\CardCode::NAME => '8888']
            ]
        ];
    }
}
