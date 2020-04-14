<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Request;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Request\AuthenticateTestRequest;

class AuthenticateTestRequestTest extends AbstractRequestTest
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->request = new AuthenticateTestRequest();
    }

    /**
     * {@inheritdoc}
     */
    public function optionsProvider()
    {
        return [
            'default' => []
        ];
    }
}
