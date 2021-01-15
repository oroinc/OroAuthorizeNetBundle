<?php

namespace Oro\Bundle\AuthorizeNetBundle\Service;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\AuthorizeNetBundle\Entity\AuthorizeNetSettings;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\SecurityBundle\Encoder\SymmetricCrypterInterface;

/**
 * Get transaction key value based on some source
 */
class TransactionKeyValueProvider
{
    /** @var ManagerRegistry */
    private $registry;

    /**  @var SymmetricCrypterInterface */
    private $crypter;

    /**
     * @param ManagerRegistry $registry
     * @param SymmetricCrypterInterface $crypter
     */
    public function __construct(
        ManagerRegistry $registry,
        SymmetricCrypterInterface $crypter
    ) {
        $this->registry = $registry;
        $this->crypter = $crypter;
    }

    /**
     * @param int|null $integrationId
     * @param string $value
     * @return string
     */
    public function fromIntegrationEditFormValue(?int $integrationId, string $value): string
    {
        if ($integrationId === null) {
            return $value;
        }

        /** @var Channel $channel */
        $channel = $this->registry
            ->getManagerForClass(Channel::class)
            ->find(Channel::class, $integrationId);

        /** @var AuthorizeNetSettings $settings */
        $settings = $channel->getTransport();
        $storedValue = $this->crypter->decryptData($settings->getTransactionKey());

        $valueLength = strlen($value);

        if (substr_count($value, '*') == $valueLength && $valueLength == strlen($storedValue)) {
            return $storedValue;
        }

        return $value;
    }
}
