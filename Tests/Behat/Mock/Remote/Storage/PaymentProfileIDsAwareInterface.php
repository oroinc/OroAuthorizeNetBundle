<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Behat\Mock\Remote\Storage;

interface PaymentProfileIDsAwareInterface
{
    public function setPaymentProfileIdsStorage(PaymentProfileIDs $paymentProfileIdsStorage);
}
