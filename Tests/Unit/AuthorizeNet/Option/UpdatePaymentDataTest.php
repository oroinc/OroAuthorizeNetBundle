<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Option;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

class UpdatePaymentDataTest extends AbstractOptionTest
{
    /** {@inheritdoc} */
    protected function getOptions()
    {
        return [new Option\UpdatePaymentData()];
    }

    /** {@inheritdoc} */
    public function configureOptionDataProvider()
    {
        return [
            'required' => [
                [],
                [],
                [
                    'Symfony\Component\OptionsResolver\Exception\MissingOptionsException',
                    'The required option "update_payment_data" is missing.',
                ],
            ],
            'invalid_value' => [
                ['update_payment_data' => 0],
                [],
                [
                    'Symfony\Component\OptionsResolver\Exception\InvalidOptionsException',
                    'The option "update_payment_data" with value 0 is expected to be of type "bool", '.
                    'but is of type "integer".',
                ],
            ],
            'valid' => [
                ['update_payment_data' => true],
                ['update_payment_data' => true],
            ]

        ];
    }
}
