<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Option;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

class CardNumberTest extends AbstractOptionTest
{
    /**
     * {@inheritDoc}
     */
    protected function getOptions(): array
    {
        return [new Option\CardNumber()];
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
                    'The required option "card_number" is missing.',
                ],
            ],
            'wrong_type' => [
                ['card_number' => 12345],
                [],
                [
                    InvalidOptionsException::class,
                    'The option "card_number" with value 12345 is expected to be of type "string", but is of '.
                    'type "int".',
                ],
            ],
            'valid' => [
                ['card_number' => 'XXXX1234'],
                ['card_number' => 'XXXX1234'],
            ],
        ];
    }
}
