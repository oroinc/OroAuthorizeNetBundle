<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Request;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Request\GetCustomerPaymentProfileRequest;

class GetCustomerPaymentProfileRequestTest extends AbstractRequestTest
{
    protected function setUp(): void
    {
        $this->request = new GetCustomerPaymentProfileRequest();
    }

    /**
     * {@inheritDoc}
     */
    public function optionsProvider(): array
    {
        return [
            'default' => [
                [
                    Option\CustomerProfileId::CUSTOMER_PROFILE_ID => '12345',
                    Option\CustomerPaymentProfileId::CUSTOMER_PAYMENT_PROFILE_ID => '12345',
                ]
            ]
        ];
    }
}
