<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Request;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Request\CaptureRequest;

class CaptureRequestTest extends AbstractRequestTest
{
    protected function setUp(): void
    {
        $this->request = new CaptureRequest();
    }

    /**
     * {@inheritdoc}
     */
    public function optionsProvider()
    {
        return [
            'default' => [
                [
                    Option\Transaction::TRANSACTION_TYPE => (new CaptureRequest())->getType(),
                    Option\Amount::AMOUNT => 10.00,
                    Option\Currency::CURRENCY => Option\Currency::US_DOLLAR,
                    Option\OriginalTransaction::ORIGINAL_TRANSACTION => 1
                ]
            ]
        ];
    }
}
