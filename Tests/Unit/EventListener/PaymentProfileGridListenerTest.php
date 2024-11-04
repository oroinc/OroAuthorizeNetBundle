<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\EventListener;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\AuthorizeNetBundle\EventListener\PaymentProfileGridListener;
use Oro\Bundle\AuthorizeNetBundle\Provider\CustomerProfileProvider;
use Oro\Bundle\AuthorizeNetBundle\Provider\PaymentProfileProvider;
use Oro\Bundle\DataGridBundle\Datagrid\Datagrid;
use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;

class PaymentProfileGridListenerTest extends \PHPUnit\Framework\TestCase
{
    private const EXTERNAL_IDS = ['external1', 'external2'];

    /** @var PaymentProfileGridListener */
    private $eventListener;

    /** @var PaymentProfileProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $paymentProfileProvider;

    /** @var CustomerProfileProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $customerProfileProvider;

    #[\Override]
    protected function setUp(): void
    {
        $this->paymentProfileProvider = $this->createMock(PaymentProfileProvider::class);
        $this->customerProfileProvider = $this->createMock(CustomerProfileProvider::class);

        $this->eventListener = new PaymentProfileGridListener(
            $this->paymentProfileProvider,
            $this->customerProfileProvider
        );
    }

    public function testOnBuildAfter()
    {
        $this->paymentProfileProvider->expects($this->once())
            ->method('getPaymentProfileExternalIds')
            ->willReturn(self::EXTERNAL_IDS);

        $datasource = $this->createMock(OrmDatasource::class);
        $datagrid = $this->createMock(Datagrid::class);
        $queryBuilder = $this->createMock(QueryBuilder::class);

        $datagrid->expects($this->once())
            ->method('getDatasource')
            ->willReturn($datasource);

        $datasource->expects($this->once())
            ->method('getQueryBuilder')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->once())
            ->method('expr')
            ->willReturn(new Expr());

        $queryBuilder->expects($this->once())
            ->method('andWhere')
            ->with(new Expr\Func('profile.customerPaymentProfileId IN', [':externalIds']));

        $queryBuilder->expects($this->once())
            ->method('setParameter')
            ->with('externalIds', self::EXTERNAL_IDS);

        $event = new BuildAfter($datagrid);

        $this->eventListener->onBuildAfter($event);
    }
}
