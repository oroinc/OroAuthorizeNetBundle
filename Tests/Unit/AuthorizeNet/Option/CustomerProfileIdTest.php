<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Option;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

class CustomerProfileIdTest extends AbstractOptionTest
{
    /**
     * {@inheritDoc}
     */
    protected function getOptions(): array
    {
        return [new Option\CustomerProfileId()];
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
                    'The required option "customer_profile_id" is missing.',
                ],
            ],
            'wrong_type' => [
                ['customer_profile_id' => 12345],
                [],
                [
                    InvalidOptionsException::class,
                    'The option "customer_profile_id" with value 12345 is expected '.
                    'to be of type "string", but is of type "int".',
                ],
            ],
            'valid' => [
                ['customer_profile_id' => '123123'],
                ['customer_profile_id' => '123123'],
            ],
        ];
    }
}
