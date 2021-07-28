<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Option;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

class CustomerPaymentProfileIdTest extends AbstractOptionTest
{
    /**
     * {@inheritdoc}
     */
    protected function getOptions()
    {
        return [new Option\CustomerPaymentProfileId()];
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
                    'The required option "customer_payment_profile_id" is missing.',
                ],
            ],
            'wrong_type' => [
                ['customer_payment_profile_id' => 12345],
                [],
                [
                    'Symfony\Component\OptionsResolver\Exception\InvalidOptionsException',
                    'The option "customer_payment_profile_id" with value 12345 is expected'.
                    ' to be of type "string", but is of type '.
                    '"int".',
                ],
            ],
            'valid' => [
                ['customer_payment_profile_id' => '123123'],
                ['customer_payment_profile_id' => '123123'],
            ],
        ];
    }
}
