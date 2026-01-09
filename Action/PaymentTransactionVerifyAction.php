<?php

namespace Oro\Bundle\AuthorizeNetBundle\Action;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Response\ResponseInterface;
use Oro\Bundle\AuthorizeNetBundle\Method\AuthorizeNetPaymentMethod;
use Oro\Bundle\PaymentBundle\Action\AbstractPaymentMethodAction;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;
use Oro\Component\ConfigExpression\ContextAccessorAwareTrait;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Action to verify transaction status and update payment transaction
 */
class PaymentTransactionVerifyAction extends AbstractPaymentMethodAction
{
    use ContextAccessorAwareTrait;

    /**
     * Transaction statuses
     */
    public const AUTHORIZED_PENDING_CAPTURE = 'authorizedPendingCapture';
    public const CAPTURE_PENDING_SETTLEMENT = 'capturedPendingSettlement';
    public const FDS_AUTHORIZED_PENDING_REVIEW = 'FDSAuthorizedPendingReview';
    public const FDS_PENDING_REVIEW = 'FDSPendingReview';
    public const DECLINED = 'declined';
    public const VOIDED = 'voided';

    protected static $matchedStatusAction = [
        self::AUTHORIZED_PENDING_CAPTURE => AuthorizeNetPaymentMethod::AUTHORIZE,
        self::CAPTURE_PENDING_SETTLEMENT => AuthorizeNetPaymentMethod::CHARGE,
        self::FDS_AUTHORIZED_PENDING_REVIEW => AuthorizeNetPaymentMethod::VERIFY,
        self::FDS_PENDING_REVIEW => AuthorizeNetPaymentMethod::VERIFY,
        self::DECLINED => self::DECLINED,
        self::VOIDED => self::VOIDED
    ];

    protected static $matchedStatusMessage = [
        self::AUTHORIZED_PENDING_CAPTURE => 'oro.authorize_net.message.approved',
        self::CAPTURE_PENDING_SETTLEMENT => 'oro.authorize_net.message.charged',
        self::FDS_AUTHORIZED_PENDING_REVIEW => 'oro.authorize_net.message.not_approved',
        self::FDS_PENDING_REVIEW => 'oro.authorize_net.message.not_approved',
        self::DECLINED => 'oro.authorize_net.message.declined',
        self::VOIDED => 'oro.authorize_net.message.voided'
    ];

    #[\Override]
    protected function configureOptionsResolver(OptionsResolver $resolver): void
    {
        parent::configureOptionsResolver($resolver);

        $resolver
            ->remove(['amount', 'currency']);
    }

    #[\Override]
    protected function configureValuesResolver(OptionsResolver $resolver): void
    {
        parent::configureValuesResolver($resolver);

        $resolver
            ->remove(['amount', 'currency']);
    }

    #[\Override]
    protected function executeAction($context): void
    {
        $options = $this->getOptions($context);
        $paymentTransaction = $this->resolveValue($context, $options['object']);

        $data = $this->executePaymentTransaction($paymentTransaction);

        if (!$data['successful'] || !$this->isValidTransaction($data)) {
            $this->setAttributeValue($context, [
                'successful' => false,
                'message' => 'oro.payment.message.error'
            ]);
            return;
        }

        $status = $data['transaction']['transaction_status'];
        $responseCode = $data['transaction']['response_code'];

        $result = $this->handleStatus($status);
        $this->updateTransaction($paymentTransaction, $responseCode, $status);

        $this->setAttributeValue($context, $result);
    }

    protected function handleStatus(string $status): array
    {
        if (!array_key_exists($status, self::$matchedStatusMessage)) {
            $result['message'] = 'oro.payment.message.error';
            $result['successful'] = false;

            return $result;
        }

        return [
            'successful' => true,
            'message' => self::$matchedStatusMessage[$status]
        ];
    }

    /**
     * @throws \Throwable
     */
    protected function updateTransaction(
        PaymentTransaction $paymentTransaction,
        int $responseCode,
        string $status
    ): void {
        $notApproved = $responseCode == ResponseInterface::TRANS_NOT_APPROVED_RESPONSE_CODE;
        if ($notApproved || !array_key_exists($status, self::$matchedStatusAction)) {
            return;
        }

        if ($status === self::VOIDED || $status === self::DECLINED) {
            $paymentTransaction
                ->setActive(false)
                ->setSuccessful(false);
        }

        $paymentTransaction->setAction(self::$matchedStatusAction[$status]);
        $this->paymentTransactionProvider->savePaymentTransaction($paymentTransaction);
    }

    protected function isValidTransaction(array $data): bool
    {
        $transaction = $data['transaction'] ?? null;
        if (
            !$transaction ||
            !array_key_exists('response_code', $transaction) ||
            !array_key_exists('transaction_status', $transaction)
        ) {
            return false;
        }

        return true;
    }
}
