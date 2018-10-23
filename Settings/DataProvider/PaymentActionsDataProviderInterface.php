<?php

namespace Oro\Bundle\AuthorizeNetBundle\Settings\DataProvider;

/**
 * Payment method action provider interface
 */
interface PaymentActionsDataProviderInterface
{
    /**
     * @return string[]
     */
    public function getPaymentActions();
}
