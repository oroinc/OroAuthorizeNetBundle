<?php

namespace Oro\Bundle\AuthorizeNetBundle\Helper;

/**
 * Helps to Generate CustomerProfileId,
 * Used in Helper\RequestSender and Method\Option\Provider\MethodOptionProvider
 * so there are two places and this helper allows to keep generation strategy consistent
 */
class MerchantCustomerIdGenerator
{
    private const ID_TPL = 'oro-%d-%d';

    public function generate(int $integrationId, int $customerUserId): string
    {
        return sprintf(self::ID_TPL, $integrationId, $customerUserId);
    }
}
