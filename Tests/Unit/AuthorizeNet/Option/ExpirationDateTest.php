<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Option;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

class ExpirationDateTest extends AbstractOptionTest
{
    /** {@inheritdoc} */
    protected function getOptions()
    {
        return [new Option\ExpirationDate()];
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
                    'The required option "expiration_date" is missing.',
                ],
            ],
            'wrong_type' => [
                ['expiration_date' => 12345],
                [],
                [
                    'Symfony\Component\OptionsResolver\Exception\InvalidOptionsException',
                    'The option "expiration_date" with value 12345 is expected to be of type "string", but is of '.
                    'type "int".',
                ],
            ],
            'valid' => [
                ['expiration_date' => 'XXXX'],
                ['expiration_date' => 'XXXX'],
            ],
        ];
    }
}
