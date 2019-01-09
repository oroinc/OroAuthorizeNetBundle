<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Behat\Mock\Remote\Storage;

interface PaymentProfileTypesToIDsAwareInterface
{
    /**
     * @param PaymentProfileTypesToIDs $paymentProfileTypesToIDs
     */
    public function setPaymentProfileTypesToIDsStorage(PaymentProfileTypesToIDs $paymentProfileTypesToIDs);
}
