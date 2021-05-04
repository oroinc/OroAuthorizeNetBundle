<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\Provider;

use Doctrine\ORM\EntityRepository;
use Oro\Bundle\AuthorizeNetBundle\Entity\CustomerProfile;
use Oro\Bundle\AuthorizeNetBundle\Provider\CustomerProfileProvider;
use Oro\Bundle\AuthorizeNetBundle\Provider\IntegrationProvider;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessor;

class CustomerProfileProviderTest extends \PHPUnit\Framework\TestCase
{
    /** @var IntegrationProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $integrationProvider;

    /** @var TokenAccessor|\PHPUnit\Framework\MockObject\MockObject */
    private $tokenAccessor;

    /** @var DoctrineHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $doctrineHelper;

    /** @var EntityRepository|\PHPUnit\Framework\MockObject\MockObject */
    private $repository;

    /** @var CustomerProfileProvider */
    private $provider;

    protected function setUp(): void
    {
        $this->doctrineHelper = $this->createMock(DoctrineHelper::class);
        $this->integrationProvider = $this->createMock(IntegrationProvider::class);
        $this->tokenAccessor = $this->createMock(TokenAccessor::class);
        $this->repository = $this->createMock(EntityRepository::class);

        $this->provider = new CustomerProfileProvider(
            $this->doctrineHelper,
            $this->integrationProvider,
            $this->tokenAccessor
        );
    }

    public function testFindCustomerProfile()
    {
        $customerUser = new CustomerUser();
        $integration = new Channel();
        $customerProfile = new CustomerProfile();

        $this->tokenAccessor
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($customerUser);

        $this->repository
            ->expects($this->once())
            ->method('findOneBy')
            ->with([
                'customerUser' => $customerUser,
                'integration' => $integration
            ])
            ->willReturn($customerProfile);

        $this->integrationProvider
            ->expects($this->once())
            ->method('getIntegration')
            ->willReturn($integration);

        $this->doctrineHelper
            ->expects($this->once())
            ->method('getEntityRepository')
            ->with(CustomerProfile::class)
            ->willReturn($this->repository);

        $result = $this->provider->findCustomerProfile();
        $this->assertEquals($customerProfile, $result);
    }
}
