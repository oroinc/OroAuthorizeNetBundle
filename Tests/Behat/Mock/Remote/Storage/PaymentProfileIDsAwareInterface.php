<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Behat\Mock\Remote\Storage;

interface PaymentProfileIDsAwareInterface
{
    /**
     * @param PaymentProfileIDs $paymentProfileIdsStorage
     */
    public function setPaymentProfileIdsStorage(PaymentProfileIDs $paymentProfileIdsStorage);
}
