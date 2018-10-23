<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Option;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

class CardNumberTest extends AbstractOptionTest
{
    /** {@inheritdoc} */
    protected function getOptions()
    {
        return [new Option\CardNumber()];
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
                    'The required option "card_number" is missing.',
                ],
            ],
            'wrong_type' => [
                ['card_number' => 12345],
                [],
                [
                    'Symfony\Component\OptionsResolver\Exception\InvalidOptionsException',
                    'The option "card_number" with value 12345 is expected to be of type "string", but is of '.
                    'type "integer".',
                ],
            ],
            'valid' => [
                ['card_number' => 'XXXX1234'],
                ['card_number' => 'XXXX1234'],
            ],
        ];
    }
}
