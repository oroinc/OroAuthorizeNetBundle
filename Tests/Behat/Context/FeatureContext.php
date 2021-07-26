<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Behat\Context;

use Oro\Bundle\AuthorizeNetBundle\Tests\Behat\Mock\Remote\Storage\PaymentProfileIDs;
use Oro\Bundle\TestFrameworkBundle\Behat\Context\OroFeatureContext;

class FeatureContext extends OroFeatureContext
{
    private PaymentProfileIDs $remotePaymentProfileIdsManager;

    public function __construct(PaymentProfileIDs $remotePaymentProfileIdsManager)
    {
        $this->remotePaymentProfileIdsManager = $remotePaymentProfileIdsManager;
    }

    /**
     * @Given /^(?:|I )remove last added payment profile from AuthorizeNet account$/
     */
    public function iRemoveLastAddedPaymentProfileFromAuthorizeNet()
    {
        $paymentProfileIds = $this->remotePaymentProfileIdsManager->all();
        if (0 === count($paymentProfileIds)) {
            self::assertNotEmpty(
                $paymentProfileIds,
                'Expect that at least one payment profile exists, but no found !'
            );
        }

        $this->remotePaymentProfileIdsManager->remove(end($paymentProfileIds));
    }

    /**
     * @Then /^number of records payment profiles in AuthorizeNet account should be (?P<count>(?:\d+))$/
     */
    public function numberOfPaymentProfilesOnAuthorizeNet(int $count)
    {
        $profileIds = $this->remotePaymentProfileIdsManager->all();

        self::assertCount(
            $count,
            $profileIds,
            sprintf(
                'Expect %d payment profiles on AuthorizeNet but got %d.',
                $count,
                count($profileIds)
            )
        );
    }
}
