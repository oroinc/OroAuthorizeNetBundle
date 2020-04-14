<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Request;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Request\DeleteCustomerPaymentProfileRequest;

class DeleteCustomerPaymentProfileRequestTest extends AbstractRequestTest
{
    protected function setUp(): void
    {
        $this->request = new DeleteCustomerPaymentProfileRequest();
    }

    /**
     * {@inheritdoc}
     */
    public function optionsProvider()
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
