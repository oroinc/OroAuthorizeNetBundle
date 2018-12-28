<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Behat\Mock\Remote\Storage;

use Doctrine\Common\Cache\CacheProvider;

class PaymentProfileIDs
{
    const CUSTOMER_PAYMENT_PROFILE_IDS = 'oro_au_net_mock_customer_payment_profile_ids';

    /** @var CacheProvider */
    private $cache;

    /**
     * @param CacheProvider $cache
     */
    public function __construct(CacheProvider $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @param string $customerPaymentProfileId
     * @return bool
     */
    public function save(string $customerPaymentProfileId)
    {
        $paymentProfileIDsCached = $this->cache->fetch(self::CUSTOMER_PAYMENT_PROFILE_IDS);
        if (false === $paymentProfileIDsCached) {
            $paymentProfileIDs = [];
        } else {
            $paymentProfileIDs = \json_decode($paymentProfileIDsCached, true);
        }

        if (\in_array($customerPaymentProfileId, $paymentProfileIDs, true)) {
            throw new \LogicException("Can't to save a profile that already exists in the storage!");
        }

        $paymentProfileIDs[] = $customerPaymentProfileId;

        $this->cache->save(self::CUSTOMER_PAYMENT_PROFILE_IDS, \json_encode($paymentProfileIDs));

        return true;
    }

    /**
     * @return array|string[]
     */
    public function all()
    {
        $paymentProfileIDsCached = $this->cache->fetch(self::CUSTOMER_PAYMENT_PROFILE_IDS);
        if (false === $paymentProfileIDsCached) {
            return [];
        }

        return \json_decode($paymentProfileIDsCached, true);
    }

    /**
     * @param string $customerPaymentProfileId
     * @return bool
     */
    public function exists(string $customerPaymentProfileId)
    {
        $paymentProfileIDsCached = $this->cache->fetch(self::CUSTOMER_PAYMENT_PROFILE_IDS);
        if (false === $paymentProfileIDsCached) {
            return false;
        }

        $paymentProfileIds = \json_decode($paymentProfileIDsCached, true);

        return \in_array($customerPaymentProfileId, $paymentProfileIds, true);
    }

    /**
     * @param string $customerPaymentProfileId
     * @return bool
     */
    public function remove(string $customerPaymentProfileId)
    {
        $paymentProfileIDsCached = $this->cache->fetch(self::CUSTOMER_PAYMENT_PROFILE_IDS);
        if (false === $paymentProfileIDsCached) {
            return false;
        }

        $paymentProfileIds = \json_decode($paymentProfileIDsCached, true);

        if (in_array($customerPaymentProfileId, $paymentProfileIds, true)) {
            $this->cache->save(
                self::CUSTOMER_PAYMENT_PROFILE_IDS,
                \json_encode(
                    \array_diff($paymentProfileIds, [$customerPaymentProfileId])
                )
            );

            return true;
        }

        return false;
    }
}
