<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Request;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Request\GetCustomerProfileRequest;

class GetCustomerProfileRequestTest extends AbstractRequestTest
{
    protected function setUp(): void
    {
        $this->request = new GetCustomerProfileRequest();
    }

    /**
     * {@inheritdoc}
     */
    public function optionsProvider()
    {
        return [
            'default' => [
                [
                    Option\CustomerProfileId::CUSTOMER_PROFILE_ID => '12345'
                ]
            ]
        ];
    }
}
