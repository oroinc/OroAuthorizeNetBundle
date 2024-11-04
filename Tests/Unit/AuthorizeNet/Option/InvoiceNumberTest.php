<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Option;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

class InvoiceNumberTest extends AbstractOptionTest
{
    #[\Override]
    protected function getOptions(): array
    {
        return [new Option\InvoiceNumber()];
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
                    sprintf('The required option "%s" is missing.', Option\InvoiceNumber::NAME)
                ]
            ],
            'wrong_type' => [
                [Option\InvoiceNumber::NAME => 123],
                [],
                [
                    InvalidOptionsException::class,
                    sprintf(
                        'The option "%s" with value 123 is expected to be of type "string", but is of '.
                        'type "int".',
                        Option\InvoiceNumber::NAME
                    )
                ]
            ],
            'valid' => [
                [Option\InvoiceNumber::NAME => '123'],
                [Option\InvoiceNumber::NAME => '123']
            ],
            'otherValid' => [
                [Option\InvoiceNumber::NAME => '8888'],
                [Option\InvoiceNumber::NAME => '8888']
            ]
        ];
    }
}
