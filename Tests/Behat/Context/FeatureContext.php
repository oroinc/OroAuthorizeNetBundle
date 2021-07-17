<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Behat\Context;

use Behat\Symfony2Extension\Context\KernelAwareContext;
use Behat\Symfony2Extension\Context\KernelDictionary;
use Oro\Bundle\AuthorizeNetBundle\Tests\Behat\Mock\Remote\Storage\PaymentProfileIDs;
use Oro\Bundle\TestFrameworkBundle\Behat\Context\OroFeatureContext;

class FeatureContext extends OroFeatureContext implements KernelAwareContext
{
    use KernelDictionary;

    /**
     * @Given /^(?:|I )remove last added payment profile from AuthorizeNet account$/
     */
    public function iRemoveLastAddedPaymentProfileFromAuthorizeNet()
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
    public function numberOfPaymentProfilesOnAuthorizeNet(int $count)
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

    /**
     * @return PaymentProfileIDs
     */
    private function getRemotePaymentProfileIdsManager()
    {
        return $this->getContainer()->get('oro_authorize_net.mock.remote.storage.payment_profile_ids');
    }
}
