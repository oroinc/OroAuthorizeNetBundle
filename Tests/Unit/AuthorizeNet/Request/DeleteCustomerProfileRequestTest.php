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
     * {@inheritdoc}
     */
    public function optionsProvider()
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
