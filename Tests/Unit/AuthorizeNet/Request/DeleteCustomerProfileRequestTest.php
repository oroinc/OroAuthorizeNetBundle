<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Request;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Request\DeleteCustomerProfileRequest;

class DeleteCustomerProfileRequestTest extends AbstractRequestTest
{
    #[\Override]
    protected function setUp(): void
    {
        $this->request = new DeleteCustomerProfileRequest();
    }

    #[\Override]
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
