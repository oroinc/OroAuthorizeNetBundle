<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Option;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

class ApiLoginIdTest extends AbstractOptionTest
{
    /**
     * {@inheritDoc}
     */
    protected function getOptions(): array
    {
        return [new Option\ApiLoginId()];
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptionDataProvider(): array
    {
        return [
            'required' => [
                [],
                [],
                [
                    MissingOptionsException::class,
                    'The required option "api_login_id" is missing.',
                ],
            ],
            'wrong_type' => [
                ['api_login_id' => 12345],
                [],
                [
                    InvalidOptionsException::class,
                    'The option "api_login_id" with value 12345 is expected to be of type "string", but is of type '.
                    '"int".',
                ],
            ],
            'valid' => [
                ['api_login_id' => 'some_login'],
                ['api_login_id' => 'some_login'],
            ],
        ];
    }
}
