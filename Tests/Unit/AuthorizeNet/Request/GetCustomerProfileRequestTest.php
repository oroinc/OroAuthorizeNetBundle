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
     * {@inheritDoc}
     */
    public function optionsProvider(): array
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
