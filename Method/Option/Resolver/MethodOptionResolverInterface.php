<?php

namespace Oro\Bundle\AuthorizeNetBundle\Method\Option\Resolver;

use Oro\Bundle\AuthorizeNetBundle\Method\Config\AuthorizeNetConfigInterface;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;

/**
 * Represents requirements for realization, that is able to
 * find/prepare array of options for passing them to Gateway
 * during sending requests to Authorize.Net api
 */
interface MethodOptionResolverInterface extends MethodOptionVerifyResolverInterface
{
    /**
     * @param AuthorizeNetConfigInterface $config
     * @param PaymentTransaction $transaction
     * @return array
     */
    public function resolvePurchase(AuthorizeNetConfigInterface $config, PaymentTransaction $transaction): array;

    /**
     * @param AuthorizeNetConfigInterface $config
     * @param PaymentTransaction $transaction
     * @return array
     */
    public function resolveAuthorize(AuthorizeNetConfigInterface $config, PaymentTransaction $transaction): array;

    /**
     * @param AuthorizeNetConfigInterface $config
     * @param PaymentTransaction $transaction
     * @return array
     */
    public function resolveCharge(AuthorizeNetConfigInterface $config, PaymentTransaction $transaction): array;

    /**
     * @param AuthorizeNetConfigInterface $config
     * @param PaymentTransaction $transaction
     * @return array
     */
    public function resolveCapture(AuthorizeNetConfigInterface $config, PaymentTransaction $transaction): array;
}
