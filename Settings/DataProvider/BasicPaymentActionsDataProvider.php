<?php

namespace Oro\Bundle\AuthorizeNetBundle\Settings\DataProvider;

/**
 * Payment method action provider
 */
class BasicPaymentActionsDataProvider implements PaymentActionsDataProviderInterface
{
    /**
     * @internal
     */
    const AUTHORIZE = 'authorize';

    /**
     * @internal
     */
    const CHARGE = 'charge';

    /**
     * @return string[]
     */
    public function getPaymentActions()
    {
        return [
            self::AUTHORIZE,
            self::CHARGE,
        ];
    }
}
