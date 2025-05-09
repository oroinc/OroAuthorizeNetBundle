<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Request;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Request\CreateCustomerProfileRequest;

class CreateCustomerProfileRequestTest extends AbstractRequestTest
{
    #[\Override]
    protected function setUp(): void
    {
        $this->request = new CreateCustomerProfileRequest();
    }

    #[\Override]
    public function optionsProvider(): array
    {
        return [
            'default' => [
                [
                    Option\MerchantCustomerId::MERCHANT_CUSTOMER_ID => '12345',
                    Option\Email::EMAIL => 'example@oroinc.com',
                ]
            ]
        ];
    }
}
