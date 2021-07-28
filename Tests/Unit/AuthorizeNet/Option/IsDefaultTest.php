<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Option;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

class IsDefaultTest extends AbstractOptionTest
{
    /** {@inheritdoc} */
    protected function getOptions()
    {
        return [new Option\IsDefault()];
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
                    'The required option "is_default" is missing.',
                ],
            ],
            'invalid_value' => [
                ['is_default' => 0],
                [],
                [
                    'Symfony\Component\OptionsResolver\Exception\InvalidOptionsException',
                    'The option "is_default" with value 0 is expected to be of type "bool", but is of type "int".',
                ],
            ],
            'valid' => [
                ['is_default' => true],
                ['is_default' => true],
            ]

        ];
    }
}
