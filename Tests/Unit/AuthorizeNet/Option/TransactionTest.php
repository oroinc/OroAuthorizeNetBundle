<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Option;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

class TransactionTest extends AbstractOptionTest
{
    #[\Override]
    protected function getOptions(): array
    {
        return [new Option\Transaction()];
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
                    'The required option "transaction_type" is missing.',
                ],
            ],
            'invalid_value' => [
                ['transaction_type' => 12345],
                [],
                [
                    InvalidOptionsException::class,
                    'The option "transaction_type" with value 12345 is invalid. Accepted values are: ' .
                    '"authOnlyTransaction", "priorAuthCaptureTransaction", "authCaptureTransaction"',
                ],
            ],
        ];
    }

    /**
     * @dataProvider validTransactionValuesDataProvider
     */
    public function testValidTransactionValues(string $transactionAction)
    {
        $transaction = new Option\Transaction();
        $resolver = new Option\OptionsResolver();

        $resolver->addOption($transaction);
        $resolved = $resolver->resolve(['transaction_type' => $transactionAction]);
        $this->assertArrayHasKey('transaction_type', $resolved);
        $this->assertEquals($transactionAction, $resolved['transaction_type']);
    }

    public function validTransactionValuesDataProvider(): array
    {
        return [
            ['authOnlyTransaction'],
            ['priorAuthCaptureTransaction'],
            ['authCaptureTransaction'],
        ];
    }
}
