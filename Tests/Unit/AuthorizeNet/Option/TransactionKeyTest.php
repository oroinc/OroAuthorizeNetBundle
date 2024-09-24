<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Option;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

class TransactionKeyTest extends AbstractOptionTest
{
    #[\Override]
    protected function getOptions(): array
    {
        return [new Option\TransactionKey()];
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
                    'The required option "transaction_key" is missing.',
                ],
            ],
            'wrong_type' => [
                ['transaction_key' => 12345],
                [],
                [
                    InvalidOptionsException::class,
                    'The option "transaction_key" with value 12345 is expected to be of type "string", but is of type '.
                    '"int".',
                ],
            ],
            'valid' => [
                ['transaction_key' => 'some_transaction_key'],
                ['transaction_key' => 'some_transaction_key'],
            ],
        ];
    }
}
