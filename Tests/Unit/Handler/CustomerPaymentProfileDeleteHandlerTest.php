<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\Handler;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\AuthorizeNetBundle\Entity\CustomerPaymentProfile;
use Oro\Bundle\AuthorizeNetBundle\Handler\CustomerPaymentProfileDeleteHandler;
use Oro\Bundle\AuthorizeNetBundle\Helper\RequestSender;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

class CustomerPaymentProfileDeleteHandlerTest extends \PHPUnit\Framework\TestCase
{
    /** @var RequestSender|\PHPUnit\Framework\MockObject\MockObject */
    private $requestSender;

    /** @var DoctrineHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $doctrineHelper;

    /** @var EntityManager|\PHPUnit\Framework\MockObject\MockObject */
    private $manager;

    /** @var CustomerPaymentProfileDeleteHandler */
    private $handler;

    protected function setUp(): void
    {
        $this->doctrineHelper = $this->createMock(DoctrineHelper::class);
        $this->requestSender = $this->createMock(RequestSender::class);
        $this->manager = $this->createMock(EntityManager::class);

        $this->handler = new CustomerPaymentProfileDeleteHandler($this->doctrineHelper, $this->requestSender);
    }

    public function testHandleDelete()
    {
        $paymentProfile = new CustomerPaymentProfile();

        $this->manager->expects($this->once())
            ->method('remove')
            ->with($paymentProfile);

        $this->manager->expects($this->once())
            ->method('flush');

        $this->doctrineHelper->expects($this->once())
            ->method('getEntityManager')
            ->with($paymentProfile)
            ->willReturn($this->manager);

        $this->requestSender->expects($this->once())
            ->method('deleteCustomerPaymentProfile')
            ->with($paymentProfile);

        $this->handler->handleDelete($paymentProfile);
    }

    public function testHandleDeleteWithApiError()
    {
        $paymentProfile = new CustomerPaymentProfile();

        $this->manager->expects($this->never())
            ->method('remove')
            ->with($paymentProfile);

        $this->manager->expects($this->never())
            ->method('flush');

        $this->doctrineHelper->expects($this->never())
            ->method('getEntityManager')
            ->with($paymentProfile)
            ->willReturn($this->manager);

        $exception = new \LogicException('api error');
        $this->requestSender->expects($this->once())
            ->method('deleteCustomerPaymentProfile')
            ->with($paymentProfile)
            ->willThrowException($exception);

        $this->expectExceptionObject($exception);

        $this->handler->handleDelete($paymentProfile);
    }
}
