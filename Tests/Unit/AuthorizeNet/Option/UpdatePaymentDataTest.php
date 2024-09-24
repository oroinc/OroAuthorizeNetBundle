<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Option;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

class UpdatePaymentDataTest extends AbstractOptionTest
{
    #[\Override]
    protected function getOptions(): array
    {
        return [new Option\UpdatePaymentData()];
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
                    'The required option "update_payment_data" is missing.',
                ],
            ],
            'invalid_value' => [
                ['update_payment_data' => 0],
                [],
                [
                    InvalidOptionsException::class,
                    'The option "update_payment_data" with value 0 is expected to be of type "bool", '.
                    'but is of type "int".',
                ],
            ],
            'valid' => [
                ['update_payment_data' => true],
                ['update_payment_data' => true],
            ]

        ];
    }
}
