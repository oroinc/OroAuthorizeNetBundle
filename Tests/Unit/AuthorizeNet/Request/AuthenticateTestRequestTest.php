<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Request;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Request\AuthenticateTestRequest;

class AuthenticateTestRequestTest extends AbstractRequestTest
{
    #[\Override]
    protected function setUp(): void
    {
        $this->request = new AuthenticateTestRequest();
    }

    #[\Override]
    public function optionsProvider(): array
    {
        return [
            'default' => []
        ];
    }
}
