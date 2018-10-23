<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\Helper;

use Oro\Bundle\AuthorizeNetBundle\Helper\MerchantCustomerIdGenerator;

class MerchantCustomerIdGeneratorTest extends \PHPUnit\Framework\TestCase
{
    public function testGenerateId()
    {
        $generator = new MerchantCustomerIdGenerator();

        $id = $generator->generate(1, 2);

        $this->assertEquals('oro-1-2', $id);
    }
}
