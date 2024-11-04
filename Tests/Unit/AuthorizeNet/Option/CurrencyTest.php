<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Option;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

class CurrencyTest extends AbstractOptionTest
{
    #[\Override]
    protected function getOptions(): array
    {
        return [new Option\Currency(false)];
    }

    #[\Override]
    public function configureOptionDataProvider(): array
    {
        return [
            'invalid type' => [
                ['currency' => 'UAH'],
                [],
                [
                    InvalidOptionsException::class,
                    'The option "currency" with value "UAH" is invalid. Accepted values are: "AUD", "USD", "CAD", '.
                    '"EUR", "GBP", "NZD".',
                ],
            ],
            'valid' => [
                ['currency' => 'USD'],
                ['currency' => 'USD'],
            ],
        ];
    }

    public function testRequired()
    {
        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage('The required option "currency" is missing.');

        $resolver = new Option\OptionsResolver();
        $currency = new Option\Currency();

        $resolver->addOption($currency);
        $resolver->resolve();
    }
}
