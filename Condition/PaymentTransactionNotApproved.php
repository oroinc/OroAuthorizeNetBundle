<?php

namespace Oro\Bundle\AuthorizeNetBundle\Condition;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Response\ResponseInterface;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;
use Oro\Component\Action\Condition\AbstractCondition;
use Oro\Component\ConfigExpression\ContextAccessorAwareInterface;
use Oro\Component\ConfigExpression\ContextAccessorAwareTrait;
use Oro\Component\ConfigExpression\Exception\InvalidArgumentException;

/**
 * Service checks transaction response code which must be '4'.
 * It means that transaction not approved in Authorize.Net account.
 */
class PaymentTransactionNotApproved extends AbstractCondition implements ContextAccessorAwareInterface
{
    use ContextAccessorAwareTrait;

    public const NAME = 'payment_transaction_not_approved';

    /**
     * @var PaymentTransaction
     */
    protected $transaction;

    #[\Override]
    public function getName(): string
    {
        return self::NAME;
    }

    #[\Override]
    public function initialize(array $options): self
    {
        if (array_key_exists('transaction', $options)) {
            $this->transaction = $options['transaction'];
        }

        if (!$this->transaction) {
            throw new InvalidArgumentException('Missing "transaction" option');
        }

        return $this;
    }

    #[\Override]
    protected function isConditionAllowed($context): bool
    {
        /** @var PaymentTransaction $transaction */
        $transaction = $this->resolveValue($context, $this->transaction);
        $response = $transaction->getResponse();

        $transResponse = $response['transaction_response'] ?? null;
        if (!$transResponse || !array_key_exists('response_code', $transResponse)) {
            return false;
        }

        return $transResponse['response_code'] === ResponseInterface::TRANS_NOT_APPROVED_RESPONSE_CODE;
    }
}
