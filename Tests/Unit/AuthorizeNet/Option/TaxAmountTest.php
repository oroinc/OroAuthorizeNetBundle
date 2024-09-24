<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Option;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

class TaxAmountTest extends AbstractOptionTest
{
    #[\Override]
    protected function getOptions(): array
    {
        return [new Option\TaxAmount()];
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
                    sprintf('The required option "%s" is missing.', Option\TaxAmount::NAME)
                ]
            ],
            'wrong_type' => [
                [Option\TaxAmount::NAME => '9.99'],
                [],
                [
                    InvalidOptionsException::class,
                    sprintf(
                        'The option "%s" with value "9.99" is expected to be of type "float" or "integer", but is of '.
                        'type "string".',
                        Option\TaxAmount::NAME
                    )
                ]
            ],
            'valid int' => [
                [Option\TaxAmount::NAME => 1],
                [Option\TaxAmount::NAME => 1]
            ],
            'valid float' => [
                [Option\TaxAmount::NAME => 9.99],
                [Option\TaxAmount::NAME => 9.99]
            ]
        ];
    }
}
