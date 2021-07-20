<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Behat\Mock\Remote\Storage;

use Doctrine\Common\Cache\CacheProvider;

class PaymentProfileTypesToIDs
{
    /** @var string */
    const CUSTOMER_PAYMENT_PROFILE_TYPES_TO_IDS = 'oro_au_net_mock_customer_payment_profile_types_to_ids';

    /** @var CacheProvider */
    private $cache;

    public function __construct(CacheProvider $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @param string $customerPaymentProfileId
     * @return string
     */
    public function getType(string $customerPaymentProfileId)
    {
        $paymentProfileTypesToIDsCached = $this->cache->fetch(self::CUSTOMER_PAYMENT_PROFILE_TYPES_TO_IDS);
        if (false === $paymentProfileTypesToIDsCached) {
            throw new \LogicException('No payment profiles found in storage "Type to IDs"!');
        }

        $usedType = null;
        $paymentProfileTypesToIDs = \json_decode($paymentProfileTypesToIDsCached, true);
        foreach ($paymentProfileTypesToIDs as $type => $ids) {
            if (\in_array($customerPaymentProfileId, $ids, true)) {
                $usedType = $type;
                break;
            }
        }

        if (null === $usedType) {
            throw new \LogicException(
                sprintf(
                    'No payment profile with identifier "%s" found in storage "Type to IDs"!',
                    $customerPaymentProfileId
                )
            );
        }

        return $usedType;
    }

    /**
     * @param string $customerPaymentProfileId
     * @param string $profileType
     * @return bool
     */
    public function saveType(string $customerPaymentProfileId, string $profileType)
    {
        $paymentProfileTypesToIDs = [];
        $paymentProfileTypesToIDsCached = $this->cache->fetch(self::CUSTOMER_PAYMENT_PROFILE_TYPES_TO_IDS);
        if (false !== $paymentProfileTypesToIDsCached) {
            $paymentProfileTypesToIDs = \json_decode($paymentProfileTypesToIDsCached, true);
        }

        if (!\array_key_exists($profileType, $paymentProfileTypesToIDs)) {
            $paymentProfileTypesToIDs[$profileType] = [];
        }

        $paymentProfileTypesToIDs[$profileType][] = $customerPaymentProfileId;
        $this->cache->save(
            self::CUSTOMER_PAYMENT_PROFILE_TYPES_TO_IDS,
            \json_encode($paymentProfileTypesToIDs)
        );

        return true;
    }

    /**
     * @param string $customerPaymentProfileId
     * @return bool
     */
    public function removeId(string $customerPaymentProfileId)
    {
        $paymentProfileTypesToIDsCached = $this->cache->fetch(self::CUSTOMER_PAYMENT_PROFILE_TYPES_TO_IDS);
        if (false === $paymentProfileTypesToIDsCached) {
            throw new \LogicException(
                'Can\'t remove payment profile, no payment profiles found in storage "Type to IDs"!'
            );
        }

        $removed = false;
        $paymentProfileTypesToIDs = \json_decode($paymentProfileTypesToIDsCached, true);
        foreach ($paymentProfileTypesToIDs as $type => &$customerPaymentProfileIDs) {
            if (\in_array($customerPaymentProfileId, $customerPaymentProfileIDs, true)) {
                $customerPaymentProfileIDs = \array_diff($customerPaymentProfileIDs, [$customerPaymentProfileId]);
                $removed = true;
                break;
            }
        }

        if ($removed === false) {
            throw new \LogicException(
                'Can\'t remove payment profile with identifier "%s",' .
                ' because it doesn\'t exist in storage "Type to IDs"!'
            );
        }

        return true;
    }

    /**
     * @param string $profileType
     * @return array
     */
    public function getIdsByType(string $profileType)
    {
        $paymentProfileTypesToIDsCached = $this->cache->fetch(self::CUSTOMER_PAYMENT_PROFILE_TYPES_TO_IDS);
        if (false === $paymentProfileTypesToIDsCached) {
            return [];
        }

        $paymentProfileTypesToIDs = \json_decode($paymentProfileTypesToIDsCached, true);
        return $paymentProfileTypesToIDs[$profileType] ?? [];
    }
}
