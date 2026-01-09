<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Option;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

class AmountTest extends AbstractOptionTest
{
    #[\Override]
    protected function getOptions(): array
    {
        return [new Option\Amount()];
    }

    #[\Override]
    public function configureOptionDataProvider(): array
    {
        return [
            'empty' => [
                [],
                [],
                [
                    MissingOptionsException::class,
                    'The required option "amount" is missing.',
                ],
            ],
            'invalid type' => [
                ['amount' => 'twenty backs'],
                [],
                [
                    InvalidOptionsException::class,
                    'The option "amount" with value "twenty backs" is expected to be of type "float" or "integer", ' .
                    'but is of type "string".',
                ],
            ],
            'valid_float' => [
                ['amount' => 10.00],
                ['amount' => 10.00],
            ],
            'valid_integer' => [
                ['amount' => 10],
                ['amount' => 10],
            ],
        ];
    }

    public function testNotRequired()
    {
        $amount = new Option\Amount(false);
        $resolver = new Option\OptionsResolver();

        $resolver->addOption($amount);
        $resolved = $resolver->resolve([]);

        $this->assertCount(0, $resolved);
    }
}
