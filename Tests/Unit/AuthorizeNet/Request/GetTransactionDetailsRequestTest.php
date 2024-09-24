<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Request;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Request\GetTransactionDetailsRequest;

class GetTransactionDetailsRequestTest extends AbstractRequestTest
{
    #[\Override]
    protected function setUp(): void
    {
        $this->request = new GetTransactionDetailsRequest();
    }

    #[\Override]
    public function optionsProvider(): array
    {
        return [
            'default' => [[Option\OriginalTransaction::ORIGINAL_TRANSACTION => 1]]
        ];
    }
}
