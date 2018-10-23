<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Request;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Request\ChargeRequest;

class ChargeRequestTest extends AbstractAuthChargeRequestTest
{
    protected function setUp()
    {
        $this->request = new ChargeRequest();
    }
}
