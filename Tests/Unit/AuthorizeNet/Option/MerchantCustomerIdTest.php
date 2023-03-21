<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Option;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

class MerchantCustomerIdTest extends AbstractOptionTest
{
    /**
     * {@inheritDoc}
     */
    protected function getOptions(): array
    {
        return [new Option\MerchantCustomerId()];
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
                    'The required option "merchant_customer_id" is missing.',
                ],
            ],
            'wrong_type' => [
                ['merchant_customer_id' => 12345],
                [],
                [
                    InvalidOptionsException::class,
                    'The option "merchant_customer_id" with value 12345 is expected '.
                    'to be of type "string", but is of type "int".',
                ],
            ],
            'valid' => [
                ['merchant_customer_id' => '123123'],
                ['merchant_customer_id' => '123123'],
            ],
        ];
    }
}
