<?php

namespace Oro\Bundle\AuthorizeNetBundle\Provider;

use Oro\Bundle\AuthorizeNetBundle\Entity\CustomerProfile;
use Oro\Bundle\AuthorizeNetBundle\Helper\RequestSender;
use Psr\Log\LoggerInterface;

/**
 * Get payment profile data by customerProfile
 */
class PaymentProfileProvider
{
    /** @var RequestSender */
    protected $requestSender;

    /** @var LoggerInterface */
    protected $logger;

    /** @var array */
    private $paymentProfileIds;

    public function __construct(RequestSender $requestSender, LoggerInterface $logger)
    {
        $this->requestSender = $requestSender;
        $this->logger = $logger;
    }

    /**
     * @param CustomerProfile|null $customerProfile
     * @return array
     */
    public function getPaymentProfileExternalIds(CustomerProfile $customerProfile = null)
    {
        if (!$customerProfile || $customerProfile->getPaymentProfiles()->isEmpty()) {
            return [];
        }

        if ($this->paymentProfileIds !== null) {
            return $this->paymentProfileIds;
        }

        $customerProfileData = $this->requestSender->getCustomerProfile($customerProfile);
        $this->paymentProfileIds = $this->extractPaymentProfileIds($customerProfileData);

        $this->checkPaymentProfiles($customerProfile, $this->paymentProfileIds);

        return $this->paymentProfileIds;
    }

    /**
     * @param array $customerProfileData
     * @return array
     */
    private function extractPaymentProfileIds(array $customerProfileData)
    {
        $paymentProfiles = $customerProfileData['payment_profiles'] ?? [];

        $paymentProfileIds = \array_map(function ($paymentProfileData) {
            return $paymentProfileData['customer_payment_profile_id'];
        }, $paymentProfiles);

        return $paymentProfileIds;
    }

    private function checkPaymentProfiles(CustomerProfile $customerProfile, array $paymentProfileIds)
    {
        foreach ($customerProfile->getPaymentProfiles() as $paymentProfile) {
            if (!\in_array($paymentProfile->getCustomerPaymentProfileId(), $paymentProfileIds, true)) {
                $this->logger->warning(
                    sprintf(
                        'Payment profile ID:%d (%s) not found in api response. Found payment profiles: %s',
                        $paymentProfile->getId(),
                        $paymentProfile->getCustomerPaymentProfileId(),
                        implode(', ', $paymentProfileIds)
                    )
                );
            }
        }
    }
}
