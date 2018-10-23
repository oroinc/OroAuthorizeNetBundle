<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Request;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Request\AuthorizeRequest;

class AuthorizeRequestTest extends AbstractAuthChargeRequestTest
{
    protected function setUp()
    {
        $this->request = new AuthorizeRequest();
    }
}
