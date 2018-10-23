<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Option;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

class EmailTest extends AbstractOptionTest
{
    /**
     * {@inheritdoc}
     */
    protected function getOptions()
    {
        return [new Option\Email()];
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptionDataProvider()
    {
        return [
            'required' => [
                [],
                [],
                [
                    'Symfony\Component\OptionsResolver\Exception\MissingOptionsException',
                    'The required option "email" is missing.',
                ],
            ],
            'wrong_type' => [
                ['email' => 12345],
                [],
                [
                    'Symfony\Component\OptionsResolver\Exception\InvalidOptionsException',
                    'The option "email" with value 12345 is expected to be of type "string", but is of type '.
                    '"integer".',
                ],
            ],
            'valid' => [
                ['email' => 'example@oroinc.com'],
                ['email' => 'example@oroinc.com'],
            ],
        ];
    }
}
