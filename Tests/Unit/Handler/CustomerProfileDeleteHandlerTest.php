<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\Handler;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\AuthorizeNetBundle\Entity\CustomerProfile;
use Oro\Bundle\AuthorizeNetBundle\Handler\CustomerProfileDeleteHandler;
use Oro\Bundle\AuthorizeNetBundle\Helper\RequestSender;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

class CustomerProfileDeleteHandlerTest extends \PHPUnit\Framework\TestCase
{
    /** @var RequestSender|\PHPUnit\Framework\MockObject\MockObject */
    private $requestSender;

    /** @var DoctrineHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $doctrineHelper;

    /** @var EntityManager|\PHPUnit\Framework\MockObject\MockObject */
    private $manager;

    /** @var CustomerProfileDeleteHandler */
    private $handler;

    #[\Override]
    protected function setUp(): void
    {
        $this->doctrineHelper = $this->createMock(DoctrineHelper::class);
        $this->requestSender = $this->createMock(RequestSender::class);
        $this->manager = $this->createMock(EntityManager::class);

        $this->handler = new CustomerProfileDeleteHandler($this->doctrineHelper, $this->requestSender);
    }

    public function testHandleDelete()
    {
        $customerProfile = new CustomerProfile();

        $this->manager->expects($this->once())
            ->method('remove')
            ->with($customerProfile);

        $this->manager->expects($this->once())
            ->method('flush');

        $this->doctrineHelper->expects($this->once())
            ->method('getEntityManager')
            ->with($customerProfile)
            ->willReturn($this->manager);

        $this->requestSender->expects($this->once())
            ->method('deleteCustomerProfile')
            ->with($customerProfile);

        $this->handler->handleDelete($customerProfile);
    }

    public function testHandleDeleteWithApiError()
    {
        $customerProfile = new CustomerProfile();

        $this->manager->expects($this->never())
            ->method('remove')
            ->with($customerProfile);

        $this->manager->expects($this->never())
            ->method('flush');

        $this->doctrineHelper->expects($this->never())
            ->method('getEntityManager')
            ->with($customerProfile)
            ->willReturn($this->manager);

        $exception = new \LogicException('api error');
        $this->requestSender->expects($this->once())
            ->method('deleteCustomerProfile')
            ->with($customerProfile)
            ->willThrowException($exception);

        $this->expectExceptionObject($exception);

        $this->handler->handleDelete($customerProfile);
    }
}
