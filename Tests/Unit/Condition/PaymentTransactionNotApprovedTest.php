<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\Condition;

use Oro\Bundle\AuthorizeNetBundle\Condition\PaymentTransactionNotApproved;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;
use Oro\Component\ConfigExpression\Condition\AbstractCondition;

class PaymentTransactionNotApprovedTest extends \PHPUnit\Framework\TestCase
{
    private PaymentTransactionNotApproved $condition;

    #[\Override]
    protected function setUp(): void
    {
        $this->condition = new PaymentTransactionNotApproved();
    }

    public function testInitialize(): void
    {
        $this->assertInstanceOf(
            AbstractCondition::class,
            $this->condition->initialize(['transaction' => true])
        );
    }

    public function testGetName(): void
    {
        $this->assertEquals(PaymentTransactionNotApproved::NAME, $this->condition->getName());
    }

    public function testIsConditionAllowedWithErrorResponse(): void
    {
        $response = ['transaction_response' => ['response_code' => 1]];
        $transaction = (new PaymentTransaction())->setResponse($response);

        $this->condition->initialize(['transaction' => $transaction]);
        $this->assertFalse($this->condition->evaluate([]));
    }

    public function testIsConditionAllowedWithValidResponse(): void
    {
        $response = ['transaction_response' => ['response_code' => '4']];
        $transaction = (new PaymentTransaction())->setResponse($response);

        $this->condition->initialize(['transaction' => $transaction]);
        $this->assertTrue($this->condition->evaluate([]));
    }
}
