<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\Entity;

use Oro\Bundle\AuthorizeNetBundle\Entity\CustomerPaymentProfile;
use Oro\Bundle\AuthorizeNetBundle\Entity\CustomerProfile;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Component\Testing\Unit\EntityTestCaseTrait;

class CustomerPaymentProfileTest extends \PHPUnit\Framework\TestCase
{
    use EntityTestCaseTrait;

    public function testAccessors()
    {
        $this->assertPropertyAccessors(
            new CustomerPaymentProfile(),
            [
                ['id', 1],
                ['name', 'custom name'],
                ['lastDigits', '4242'],
                ['default', true],
                ['customerPaymentProfileId', '123456789'],
                ['customerProfile', new CustomerProfile()],
                ['customerUser', new CustomerUser()],
                ['organization', new Organization()],
            ]
        );
    }
}
