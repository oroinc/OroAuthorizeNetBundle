<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Behat\Mock\Remote\Storage;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class PaymentProfileTypesToIDs
{
    private const CUSTOMER_PAYMENT_PROFILE_TYPES_TO_IDS = 'oro_au_net_mock_customer_payment_profile_types_to_ids';

    private CacheItemPoolInterface $cache;

    public function __construct(CacheItemPoolInterface $cache)
    {
        $this->cache = $cache;
    }

    public function getType(string $customerPaymentProfileId): string
    {
        $paymentProfileTypesToIDsCachedItem = $this->getCachedItem();
        if (!$paymentProfileTypesToIDsCachedItem->isHit()) {
            throw new \LogicException('No payment profiles found in storage "Type to IDs"!');
        }

        $paymentProfileTypesToIDsCached = $paymentProfileTypesToIDsCachedItem->get();
        $usedType = null;
        $paymentProfileTypesToIDs = json_decode($paymentProfileTypesToIDsCached, true);
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

    public function saveType(string $customerPaymentProfileId, string $profileType): bool
    {
        $paymentProfileTypesToIDs = [];
        $paymentProfileTypesToIDsCachedItem = $this->getCachedItem();
        if ($paymentProfileTypesToIDsCachedItem->isHit()) {
            $paymentProfileTypesToIDs = json_decode($paymentProfileTypesToIDsCachedItem->get(), true);
        }

        if (!\array_key_exists($profileType, $paymentProfileTypesToIDs)) {
            $paymentProfileTypesToIDs[$profileType] = [];
        }

        $paymentProfileTypesToIDs[$profileType][] = $customerPaymentProfileId;
        $paymentProfileTypesToIDsCachedItem->set(json_encode($paymentProfileTypesToIDs));
        $this->cache->save($paymentProfileTypesToIDsCachedItem);

        return true;
    }

    public function removeId(string $customerPaymentProfileId): bool
    {
        $paymentProfileTypesToIDsCachedItem = $this->getCachedItem();
        if (!$paymentProfileTypesToIDsCachedItem->isHit()) {
            throw new \LogicException(
                'Can\'t remove payment profile, no payment profiles found in storage "Type to IDs"!'
            );
        }

        $removed = false;
        $paymentProfileTypesToIDs = json_decode($paymentProfileTypesToIDsCachedItem->get(), true);
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

    public function getIdsByType(string $profileType): array
    {
        $paymentProfileTypesToIDsCachedItem = $this->getCachedItem();
        if (!$paymentProfileTypesToIDsCachedItem->isHit()) {
            return [];
        }

        $paymentProfileTypesToIDs = json_decode($paymentProfileTypesToIDsCachedItem->get(), true);
        return $paymentProfileTypesToIDs[$profileType] ?? [];
    }

    private function getCachedItem(): CacheItemInterface
    {
        return $this->cache->getItem(self::CUSTOMER_PAYMENT_PROFILE_TYPES_TO_IDS);
    }
}
