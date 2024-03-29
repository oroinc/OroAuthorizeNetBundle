<?php

namespace Oro\Bundle\AuthorizeNetBundle\EventListener;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Response\ResponseInterface;
use Oro\Bundle\AuthorizeNetBundle\Event\TransactionResponseReceivedEvent;
use Oro\Bundle\AuthorizeNetBundle\Exception\TransactionLimitReachedException;
use Oro\Bundle\AuthorizeNetBundle\Method\AuthorizeNetPaymentMethod;
use Oro\Bundle\AuthorizeNetBundle\Method\Config\AuthorizeNetConfig;
use Oro\Bundle\AuthorizeNetBundle\Method\Config\Provider\AuthorizeNetConfigProviderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Set verify action for not approved transaction (listen for TransactionResponseReceivedEvent)
 */
class FraudDetectionTransactionListener
{
    public function __construct(
        protected AuthorizeNetConfigProviderInterface $configProvider,
        protected RequestStack $requestStack,
        protected TranslatorInterface $translator
    ) {
    }

    public function onTransactionResponseReceived(TransactionResponseReceivedEvent $event): void
    {
        $response = $event->getResponse();
        $paymentTransaction = $event->getPaymentTransaction();

        if (!$this->isNotApprovedTransaction($response->getData())) {
            return;
        }

        /** @var AuthorizeNetConfig $config */
        $config = $this->configProvider->getPaymentConfig($paymentTransaction->getPaymentMethod());
        if (!$config->isAllowHoldTransaction()) {
            $message = $this->translator->trans('oro.authorize_net.message.allow_hold_transaction');
            $this->requestStack->getSession()->getFlashBag()->add('warning', $message);

            throw new TransactionLimitReachedException('The transaction limit reached.');
        }

        $paymentTransaction
            ->setAction(AuthorizeNetPaymentMethod::VERIFY);
    }

    protected function isNotApprovedTransaction(array $data): bool
    {
        $response = $data['transaction_response'] ?? null;
        if (!$response || !array_key_exists('response_code', $response)) {
            return false;
        }

        return $response['response_code'] === ResponseInterface::TRANS_NOT_APPROVED_RESPONSE_CODE;
    }
}
