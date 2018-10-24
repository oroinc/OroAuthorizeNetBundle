<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\Entity\Model\DTO;

use Oro\Bundle\AuthorizeNetBundle\Model\DTO\PaymentProfileMaskedDataDTO;
use Oro\Component\Testing\Unit\EntityTestCaseTrait;

class PaymentProfileMaskedDataDTOTest extends \PHPUnit\Framework\TestCase
{
    use EntityTestCaseTrait;

    public function testAccessors()
    {
        $this->assertPropertyAccessors(
            new PaymentProfileMaskedDataDTO(),
            [
                ['accountNumber', '352525252'],
                ['routingNumber', '3424242'],
                ['nameOnAccount', 'name'],
                ['accountType', 'checking'],
                ['bankName', 'bank name', '']
            ]
        );
    }
}
