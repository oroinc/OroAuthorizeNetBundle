<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\Provider;

use Oro\Bundle\AuthorizeNetBundle\Entity\CustomerPaymentProfile;
use Oro\Bundle\AuthorizeNetBundle\Entity\CustomerProfile;
use Oro\Bundle\AuthorizeNetBundle\Helper\RequestSender;
use Oro\Bundle\AuthorizeNetBundle\Provider\CustomerProfileProvider;
use Oro\Bundle\AuthorizeNetBundle\Provider\PaymentProfileProvider;
use Psr\Log\LoggerInterface;

class PaymentProfileProviderTest extends \PHPUnit\Framework\TestCase
{
    /** @var RequestSender|\PHPUnit\Framework\MockObject\MockObject */
    private $requestSender;

    /** @var LoggerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $logger;

    /** @var CustomerProfileProvider */
    private $provider;

    protected function setUp()
    {
        $this->requestSender = $this->createMock(RequestSender::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->provider = new PaymentProfileProvider($this->requestSender, $this->logger);
    }

    public function testGetPaymentProfileExternalIdsOneValidOneInvalid()
    {
        $customerProfile = new CustomerProfile();
        $paymentProfile1 = new CustomerPaymentProfile();
        $paymentProfile1->setCustomerPaymentProfileId('external-id-1');
        $paymentProfile2 = new CustomerPaymentProfile();
        $paymentProfile2->setCustomerPaymentProfileId('external-id-2');

        $customerProfile->addPaymentProfile($paymentProfile1);
        $customerProfile->addPaymentProfile($paymentProfile2);

        $this->requestSender
            ->expects($this->once())
            ->method('getCustomerProfile')
            ->willReturn(
                [
                    'payment_profiles' => [
                        ['customer_payment_profile_id' => 'external-id-1']
                    ]
                ]
            );

        $this->logger
            ->expects($this->once())
            ->method('warning');

        $result = $this->provider->getPaymentProfileExternalIds($customerProfile);
        $this->assertEquals(['external-id-1'], $result);

        // second call will return cached result
        $result = $this->provider->getPaymentProfileExternalIds($customerProfile);
        $this->assertEquals(['external-id-1'], $result);
    }

    public function testGetPaymentProfileExternalIdsEmptyFromApi()
    {
        $customerProfile = new CustomerProfile();
        $paymentProfile = new CustomerPaymentProfile();
        $paymentProfile->setCustomerPaymentProfileId('external-id-1');

        $customerProfile->addPaymentProfile($paymentProfile);

        $this->requestSender
            ->expects($this->once())
            ->method('getCustomerProfile')
            ->willReturn([]);

        $this->logger
            ->expects($this->once())
            ->method('warning');

        $result = $this->provider->getPaymentProfileExternalIds($customerProfile);
        $this->assertEquals([], $result);
    }

    public function testGetPaymentProfileExternalIdsNoCustomerProfile()
    {
        $this->requestSender
            ->expects($this->never())
            ->method('getCustomerProfile');

        $this->logger
            ->expects($this->never())
            ->method('warning');

        $result = $this->provider->getPaymentProfileExternalIds(null);
        $this->assertSame([], $result);
    }

    public function testGetPaymentProfileExternalIdsEmptyPaymentProfiles()
    {
        $this->requestSender
            ->expects($this->never())
            ->method('getCustomerProfile');

        $this->logger
            ->expects($this->never())
            ->method('warning');

        $result = $this->provider->getPaymentProfileExternalIds(new CustomerProfile());
        $this->assertSame([], $result);
    }
}
