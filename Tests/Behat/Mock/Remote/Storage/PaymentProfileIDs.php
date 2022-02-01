<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Behat\Mock\Remote\Storage;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class PaymentProfileIDs
{
    const CUSTOMER_PAYMENT_PROFILE_IDS = 'oro_au_net_mock_customer_payment_profile_ids';

    private CacheItemPoolInterface $cache;

    public function __construct(CacheItemPoolInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @param string $customerPaymentProfileId
     * @return bool
     */
    public function save(string $customerPaymentProfileId)
    {
        $paymentProfileIDsCachedItem = $this->getCachedItem();
        if (!$paymentProfileIDsCachedItem->isHit()) {
            $paymentProfileIDs = [];
        } else {
            $paymentProfileIDs = \json_decode($paymentProfileIDsCachedItem->get(), true);
        }

        if (\in_array($customerPaymentProfileId, $paymentProfileIDs, true)) {
            throw new \LogicException("Can't to save a profile that already exists in the storage!");
        }

        $paymentProfileIDs[] = $customerPaymentProfileId;
        $paymentProfileIDsCachedItem->set(\json_encode($paymentProfileIDs));
        $this->saveCachedItem($paymentProfileIDsCachedItem);

        return true;
    }

    /**
     * @return array|string[]
     */
    public function all()
    {
        $paymentProfileIDsCachedItem = $this->getCachedItem();
        if (!$paymentProfileIDsCachedItem->isHit()) {
            return [];
        }

        return \json_decode($paymentProfileIDsCachedItem->get(), true);
    }

    /**
     * @param string $customerPaymentProfileId
     * @return bool
     */
    public function exists(string $customerPaymentProfileId)
    {
        $paymentProfileIDsCachedItem = $this->getCachedItem();
        if (!$paymentProfileIDsCachedItem->isHit()) {
            return false;
        }

        $paymentProfileIds = \json_decode($paymentProfileIDsCachedItem->get(), true);

        return \in_array($customerPaymentProfileId, $paymentProfileIds, true);
    }

    /**
     * @param string $customerPaymentProfileId
     * @return bool
     */
    public function remove(string $customerPaymentProfileId)
    {
        $paymentProfileIDsCachedItem = $this->getCachedItem();
        if (!$paymentProfileIDsCachedItem->isHit()) {
            return false;
        }

        $paymentProfileIds = \json_decode($paymentProfileIDsCachedItem->get(), true);

        if (in_array($customerPaymentProfileId, $paymentProfileIds, true)) {
            $paymentProfileIDsCachedItem->set(\json_encode(
                \array_diff($paymentProfileIds, [$customerPaymentProfileId])
            ));
            $this->saveCachedItem($paymentProfileIDsCachedItem);

            return true;
        }

        return false;
    }

    private function getCachedItem() : CacheItemInterface
    {
        return $this->cache->getItem(self::CUSTOMER_PAYMENT_PROFILE_IDS);
    }

    private function saveCachedItem(CacheItemInterface $item) : void
    {
        $this->cache->save($item);
    }
}
