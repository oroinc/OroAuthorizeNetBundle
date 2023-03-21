<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Request;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Request\GetTransactionDetailsRequest;

class GetTransactionDetailsRequestTest extends AbstractRequestTest
{
    protected function setUp(): void
    {
        $this->request = new GetTransactionDetailsRequest();
    }

    /**
     * {@inheritDoc}
     */
    public function optionsProvider(): array
    {
        return [
            'default' => [[Option\OriginalTransaction::ORIGINAL_TRANSACTION => 1]]
        ];
    }
}
