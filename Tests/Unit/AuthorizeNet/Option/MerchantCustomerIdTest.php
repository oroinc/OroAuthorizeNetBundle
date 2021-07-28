<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Option;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

class MerchantCustomerIdTest extends AbstractOptionTest
{
    /**
     * {@inheritdoc}
     */
    protected function getOptions()
    {
        return [new Option\MerchantCustomerId()];
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
                    'The required option "merchant_customer_id" is missing.',
                ],
            ],
            'wrong_type' => [
                ['merchant_customer_id' => 12345],
                [],
                [
                    'Symfony\Component\OptionsResolver\Exception\InvalidOptionsException',
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
