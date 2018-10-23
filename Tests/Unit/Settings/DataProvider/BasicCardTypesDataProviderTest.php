<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\Settings\DataProvider;

use Oro\Bundle\AuthorizeNetBundle\Settings\DataProvider\BasicCardTypesDataProvider;

class BasicCardTypesDataProviderTest extends \PHPUnit\Framework\TestCase
{
    public function testGetPaymentActions()
    {
        $provider = new BasicCardTypesDataProvider();

        $this->assertEquals(
            [
                'visa',
                'mastercard',
                'discover',
                'american_express',
                'jcb',
                'diners_club',
                'china_union_pay'
            ],
            $provider->getCardTypes()
        );
    }
}
