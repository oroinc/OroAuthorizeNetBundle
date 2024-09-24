<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Option;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

class OriginalTransactionTest extends AbstractOptionTest
{
    #[\Override]
    protected function getOptions(): array
    {
        return [new Option\OriginalTransaction(false)];
    }

    #[\Override]
    public function configureOptionDataProvider(): array
    {
        return [
            'invalid type' => [
                ['original_transaction' => 123.456],
                [],
                [
                    InvalidOptionsException::class,
                    'The option "original_transaction" with value 123.456 is expected to be of type '.
                    '"integer" or "string", but is of type "float".',
                ],
            ],
            'valid_string' => [
                ['original_transaction' => '1'],
                ['original_transaction' => '1'],
            ],
            'valid_integer' => [
                ['original_transaction' => 1],
                ['original_transaction' => 1],
            ],
        ];
    }

    public function testRequired()
    {
        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage('The required option "original_transaction" is missing.');

        $originalTransaction = new Option\OriginalTransaction();

        $resolver = new Option\OptionsResolver();
        $resolver->addOption($originalTransaction);
        $resolver->resolve();
    }
}
