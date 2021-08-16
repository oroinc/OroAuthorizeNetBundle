<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Behat\Context;

use Oro\Bundle\AuthorizeNetBundle\Tests\Behat\Mock\Remote\Storage\PaymentProfileIDs;
use Oro\Bundle\TestFrameworkBundle\Behat\Context\OroFeatureContext;

class FeatureContext extends OroFeatureContext
{
    /**
     * @Given /^(?:|I )remove last added payment profile from AuthorizeNet account$/
     */
    public function iRemoveLastAddedPaymentProfileFromAuthorizeNet(): void
    {
        $managerPaymentProfileIds = $this->getRemotePaymentProfileIdsManager();

        $paymentProfileIds = $managerPaymentProfileIds->all();
        if (0 === count($paymentProfileIds)) {
            self::assertNotEmpty(
                $paymentProfileIds,
                'Expect that at least one payment profile exists, but no found !'
            );
        }

        $managerPaymentProfileIds->remove(end($paymentProfileIds));
    }

    /**
     * @Then /^number of records payment profiles in AuthorizeNet account should be (?P<count>(?:\d+))$/
     */
    public function numberOfPaymentProfilesOnAuthorizeNet(int $count): void
    {
        $managerPaymentProfileIds = $this->getRemotePaymentProfileIdsManager();
        $profileIds = $managerPaymentProfileIds->all();

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

    private function getRemotePaymentProfileIdsManager(): PaymentProfileIDs
    {
        return $this->getAppContainer()->get('oro_authorize_net.mock.remote.storage.payment_profile_ids');
    }
}
