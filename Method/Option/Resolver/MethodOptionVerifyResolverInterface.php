<?php

namespace Oro\Bundle\AuthorizeNetBundle\Method\Option\Resolver;

use Oro\Bundle\AuthorizeNetBundle\Method\Config\AuthorizeNetConfigInterface;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;

/**
 * Represents verify method as a requirement for preparing a request
 */
interface MethodOptionVerifyResolverInterface
{
    /**
     * @param AuthorizeNetConfigInterface $config
     * @param PaymentTransaction $transaction
     * @return array
     */
    public function resolveVerify(AuthorizeNetConfigInterface $config, PaymentTransaction $transaction): array;
}
