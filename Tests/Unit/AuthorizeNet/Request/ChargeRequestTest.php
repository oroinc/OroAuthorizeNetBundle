<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Request;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Request\ChargeRequest;

class ChargeRequestTest extends AbstractAuthChargeRequestTest
{
    #[\Override]
    protected function setUp(): void
    {
        $this->request = new ChargeRequest();
    }
}
