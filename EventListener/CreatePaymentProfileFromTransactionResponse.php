<?php

namespace Oro\Bundle\AuthorizeNetBundle\EventListener;

use Oro\Bundle\AuthorizeNetBundle\Entity\CustomerPaymentProfile;
use Oro\Bundle\AuthorizeNetBundle\Entity\CustomerProfile;
use Oro\Bundle\AuthorizeNetBundle\Event\TransactionResponseReceivedEvent;
use Oro\Bundle\AuthorizeNetBundle\Provider\IntegrationProvider;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\OrderBundle\Entity\Order;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Create payment profile from transaction response (listen for TransactionResponseReceivedEvent)
 */
class CreatePaymentProfileFromTransactionResponse
{
    public const ACCOUNT_TYPE_ECHECK = 'eCheck';

    /** @var DoctrineHelper */
    private $doctrineHelper;

    /** @var IntegrationProvider */
    private $integrationProvider;

    /** @var Session */
    private $session;

    /** @var TranslatorInterface */
    private $translator;

    public function __construct(
        DoctrineHelper $doctrineHelper,
        IntegrationProvider $integrationProvider,
        Session $session,
        TranslatorInterface $translator
    ) {
        $this->doctrineHelper = $doctrineHelper;
        $this->integrationProvider = $integrationProvider;
        $this->session = $session;
        $this->translator = $translator;
    }

    public function onTransactionResponseReceived(TransactionResponseReceivedEvent $event)
    {
        if (!$this->isApplicable($event)) {
            return;
        }

        $response = $event->getResponse();
        $data = $response->getData();
        $profileData = $data['profile_response'];

        $customerProfileId = $profileData['customer_profile_id'];
        $paymentProfileId = reset($profileData['customer_payment_profile_id_list']);
        $transactionData = $data['transaction_response'];
        $profileType = $transactionData['account_type'] === self::ACCOUNT_TYPE_ECHECK
            ? CustomerPaymentProfile::TYPE_ECHECK
            : CustomerPaymentProfile::TYPE_CREDITCARD;
        $accountNumber = $transactionData['account_number'];
        $lastDigits = substr($accountNumber, -4);

        /** @var CustomerProfile $customerProfile */
        $customerProfile = $this
            ->doctrineHelper
            ->getEntityRepository(CustomerProfile::class)
            ->findOneBy(['customerProfileId' => $customerProfileId]);

        /** @var CustomerPaymentProfile $paymentProfile */
        $paymentProfile = $this
            ->doctrineHelper
            ->getEntityRepository(CustomerPaymentProfile::class)
            ->findOneBy(['customerPaymentProfileId' => $paymentProfileId]);

        if (!$customerProfile) {
            $transaction = $event->getPaymentTransaction();
            $website = $this->getWebsite($transaction);
            $integration = $this->integrationProvider->getIntegration($website);

            $customerProfile = new CustomerProfile();
            $customerProfile->setCustomerUser($transaction->getFrontendOwner());
            $customerProfile->setIntegration($integration);
            $customerProfile->setCustomerProfileId($customerProfileId);
        }

        if (!$paymentProfile) {
            $paymentProfile = new CustomerPaymentProfile($profileType);
            $paymentProfile->setCustomerUser($customerProfile->getCustomerUser());
            $paymentProfile->setCustomerProfile($customerProfile);
            $paymentProfile->setCustomerPaymentProfileId($paymentProfileId);
            $paymentProfile->setLastDigits($lastDigits);
            $paymentProfile->setName(sprintf('****%s', $lastDigits));

            $customerProfile->addPaymentProfile($paymentProfile);

            try {
                $manager = $this->doctrineHelper->getEntityManager($customerProfile);
                $manager->persist($customerProfile);
                $manager->flush();
            } catch (\Exception $exception) {
                $this->addFlashMessage($exception->getMessage());
            }
        }
    }

    /**
     * @param TransactionResponseReceivedEvent $event
     * @return bool
     */
    private function isApplicable(TransactionResponseReceivedEvent $event)
    {
        $response = $event->getResponse();
        $responseData = $response->getData();

        if (!$event->getPaymentTransaction()->getFrontendOwner()) {
            return false;
        }

        if (array_key_exists('profile_response', $responseData)) {
            $message = $responseData['profile_response']['messages'] ?? null;
            $resultCode = $message['result_code'] ?? null;
            $applicable = ($resultCode === 'Ok');

            if ($resultCode === 'Error') {
                $messages = $message['message'] ?? [];
                $this->showErrorMessage($messages);
            }
        } else {
            $applicable = false;
        }

        return $applicable;
    }

    private function showErrorMessage(array $messages)
    {
        $messages = array_map(function ($item) {
            return $item['text'];
        }, $messages);

        $errorMessage = $this->translator->trans(
            'oro.authorize_net.frontend.payment_profile.message.not_saved',
            ['%reason%' => implode(';', $messages)]
        );

        $this->addFlashMessage($errorMessage);
    }

    /**
     * @param PaymentTransaction $transaction
     * @return Website|null
     */
    private function getWebsite(PaymentTransaction $transaction)
    {
        $order = $this->doctrineHelper->getEntity($transaction->getEntityClass(), $transaction->getEntityIdentifier());

        return ($order instanceof Order) ? $order->getWebsite() : null;
    }

    /**
     * @param string $message
     */
    private function addFlashMessage($message)
    {
        if ($this->session->isStarted()) {
            $this->session->getFlashBag()->add('warning', $message);
        }
    }
}
