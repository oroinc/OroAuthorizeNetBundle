<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\Entity;

use Oro\Bundle\AuthorizeNetBundle\Entity\CustomerProfile;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Component\Testing\Unit\EntityTestCaseTrait;

class CustomerProfileTest extends \PHPUnit\Framework\TestCase
{
    use EntityTestCaseTrait;

    public function testAccessors()
    {
        $this->assertPropertyAccessors(
            new CustomerProfile(),
            [
                ['id', 1],
                ['customerProfileId', '123456789'],
                ['integration', new Channel()],
                ['customerUser', new CustomerUser()],
                ['organization', new Organization()],
            ]
        );
    }
}
