<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Request;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Request\DeleteCustomerProfileRequest;

class DeleteCustomerProfileRequestTest extends AbstractRequestTest
{
    protected function setUp(): void
    {
        $this->request = new DeleteCustomerProfileRequest();
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
                ]
            ]
        ];
    }
}
