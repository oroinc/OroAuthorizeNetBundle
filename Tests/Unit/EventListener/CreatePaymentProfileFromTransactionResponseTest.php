<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\EventListener;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Response\AuthorizeNetSDKTransactionResponse;
use Oro\Bundle\AuthorizeNetBundle\Entity\CustomerPaymentProfile;
use Oro\Bundle\AuthorizeNetBundle\Entity\CustomerProfile;
use Oro\Bundle\AuthorizeNetBundle\Event\TransactionResponseReceivedEvent;
use Oro\Bundle\AuthorizeNetBundle\EventListener\CreatePaymentProfileFromTransactionResponse;
use Oro\Bundle\AuthorizeNetBundle\Provider\IntegrationProvider;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\OrderBundle\Entity\Order;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Contracts\Translation\TranslatorInterface;

class CreatePaymentProfileFromTransactionResponseTest extends \PHPUnit\Framework\TestCase
{
    private const CUSTOMER_PROFILE_ID = '111';
    private const PAYMENT_PROFILE_ID = '222';
    private const LAST_DIGITS = '1234';

    /** @var CreatePaymentProfileFromTransactionResponse */
    private $eventListener;

    /** @var DoctrineHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $doctrineHelper;

    /** @var IntegrationProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $integrationProvider;

    /** @var RequestStack|\PHPUnit\Framework\MockObject\MockObject */
    private $requestStack;

    /** @var TranslatorInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $translator;

    /** @var Channel */
    private $integration;

    /** @var CustomerUser */
    private $customerUser;

    /** @var CustomerProfile */
    private $customerProfile;

    /** @var CustomerPaymentProfile */
    private $paymentProfile;

    #[\Override]
    protected function setUp(): void
    {
        $this->doctrineHelper = $this->createMock(DoctrineHelper::class);
        $this->integrationProvider = $this->createMock(IntegrationProvider::class);
        $this->requestStack = $this->createMock(RequestStack::class);
        $this->translator = $this->createMock(TranslatorInterface::class);

        $this->eventListener = new CreatePaymentProfileFromTransactionResponse(
            $this->doctrineHelper,
            $this->integrationProvider,
            $this->requestStack,
            $this->translator
        );

        $this->integration = new Channel();
        $this->customerUser = new CustomerUser();
        $this->customerProfile = new CustomerProfile();
        $this->paymentProfile = new CustomerPaymentProfile();
    }

    public function testOnTransactionResponseReceivedCreateNone()
    {
        $customerProfile = $this->customerProfile;
        $paymentProfile = $this->paymentProfile;
        $transaction = $this->createTransaction();

        $response = $this->createMock(AuthorizeNetSDKTransactionResponse::class);
        $response->expects($this->exactly(2))
            ->method('getData')
            ->willReturn($this->buildResponseData(true));

        $customerProfileRepository = $this->createMock(EntityRepository::class);
        $customerProfileRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['customerProfileId' => self::CUSTOMER_PROFILE_ID], null)
            ->willReturn($customerProfile);

        $paymentProfileRepository = $this->createMock(EntityRepository::class);
        $paymentProfileRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['customerPaymentProfileId' => self::PAYMENT_PROFILE_ID], null)
            ->willReturn($paymentProfile);

        $this->doctrineHelper->expects($this->any())
            ->method('getEntityRepository')
            ->willReturnMap([
                [CustomerProfile::class, $customerProfileRepository],
                [CustomerPaymentProfile::class, $paymentProfileRepository]
            ]);

        $manager = $this->createMock(EntityManager::class);
        $manager->expects($this->never())
            ->method('persist')
            ->with($paymentProfile);
        $manager->expects($this->never())
            ->method('flush');

        $this->doctrineHelper->expects($this->never())
            ->method('getEntityManager')
            ->with($paymentProfile)
            ->willReturn($manager);

        $event = new TransactionResponseReceivedEvent($response, $transaction);
        $this->eventListener->onTransactionResponseReceived($event);
    }

    /**
     * @dataProvider successfulResponseDataProvider
     */
    public function testOnTransactionResponseReceivedCreateAll(array $responseData, string $expectedProfileType)
    {
        $transaction = $this->createTransaction();
        $transaction->setFrontendOwner($this->customerUser);
        $transaction->setEntityClass(Order::class);
        $transaction->setEntityIdentifier(1);
        $website = new Website();
        $order = new Order();
        $order->setWebsite($website);

        $this->integrationProvider->expects($this->once())
            ->method('getIntegration')
            ->with($website)
            ->willReturn($this->integration);

        $this->doctrineHelper->expects($this->once())
            ->method('getEntity')
            ->with($transaction->getEntityClass(), $transaction->getEntityIdentifier())
            ->willReturn($order);

        $response = $this->createMock(AuthorizeNetSDKTransactionResponse::class);
        $response->expects($this->exactly(2))
            ->method('getData')
            ->willReturn($responseData);

        $customerProfileRepository = $this->createMock(EntityRepository::class);
        $customerProfileRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['customerProfileId' => self::CUSTOMER_PROFILE_ID], null)
            ->willReturn(null);

        $paymentProfileRepository = $this->createMock(EntityRepository::class);
        $paymentProfileRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['customerPaymentProfileId' => self::PAYMENT_PROFILE_ID], null)
            ->willReturn(null);

        $this->doctrineHelper->expects($this->any())
            ->method('getEntityRepository')
            ->willReturnMap([
                [CustomerProfile::class, $customerProfileRepository],
                [CustomerPaymentProfile::class, $paymentProfileRepository]
            ]);

        $manager = $this->createMock(EntityManager::class);
        $manager->expects($this->once())
            ->method('persist')
            ->willReturnCallback(function (CustomerProfile $customerProfile) use ($expectedProfileType) {
                /** @var CustomerPaymentProfile $paymentProfile */
                $paymentProfile = $customerProfile->getPaymentProfiles()->first();

                $this->assertSame($this->integration, $customerProfile->getIntegration());
                $this->assertSame(self::CUSTOMER_PROFILE_ID, $customerProfile->getCustomerProfileId());
                $this->assertSame($this->customerUser, $customerProfile->getCustomerUser());

                $this->assertSame($this->customerUser, $paymentProfile->getCustomerUser());
                $this->assertSame(self::PAYMENT_PROFILE_ID, $paymentProfile->getCustomerPaymentProfileId());
                $this->assertSame(self::LAST_DIGITS, $paymentProfile->getLastDigits());
                $this->assertSame('****' . self::LAST_DIGITS, $paymentProfile->getName());
                $this->assertSame($expectedProfileType, $paymentProfile->getType());
            });
        $manager->expects($this->once())
            ->method('flush');

        $this->doctrineHelper->expects($this->once())
            ->method('getEntityManager')
            ->willReturn($manager);

        $event = new TransactionResponseReceivedEvent($response, $transaction);
        $this->eventListener->onTransactionResponseReceived($event);
    }

    /**
     * @dataProvider successfulResponseDataProvider
     */
    public function testOnTransactionResponseReceivedCreatePaymentProfileOnly(
        array $responseData,
        string $expectedProfileType
    ) {
        $transaction = $this->createTransaction();
        $customerProfile = $this->customerProfile;
        $customerProfile->setCustomerUser($this->customerUser);

        $this->integrationProvider->expects($this->never())
            ->method('getIntegration');

        $this->doctrineHelper->expects($this->never())
            ->method('getEntity');

        $response = $this->createMock(AuthorizeNetSDKTransactionResponse::class);
        $response->expects($this->exactly(2))
            ->method('getData')
            ->willReturn($responseData);

        $customerProfileRepository = $this->createMock(EntityRepository::class);
        $customerProfileRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['customerProfileId' => self::CUSTOMER_PROFILE_ID], null)
            ->willReturn($customerProfile);

        $paymentProfileRepository = $this->createMock(EntityRepository::class);
        $paymentProfileRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['customerPaymentProfileId' => self::PAYMENT_PROFILE_ID], null)
            ->willReturn(null);

        $this->doctrineHelper->expects($this->any())
            ->method('getEntityRepository')
            ->willReturnMap([
                [CustomerProfile::class, $customerProfileRepository],
                [CustomerPaymentProfile::class, $paymentProfileRepository]
            ]);

        $manager = $this->createMock(EntityManager::class);
        $manager->expects($this->once())
            ->method('persist')
            ->willReturnCallback(function (CustomerProfile $customerProfile) use ($expectedProfileType) {
                /** @var CustomerPaymentProfile $paymentProfile */
                $paymentProfile = $customerProfile->getPaymentProfiles()->first();
                $this->assertSame($customerProfile, $paymentProfile->getCustomerProfile());
                $this->assertSame($customerProfile->getCustomerUser(), $paymentProfile->getCustomerUser());
                $this->assertSame(self::PAYMENT_PROFILE_ID, $paymentProfile->getCustomerPaymentProfileId());
                $this->assertSame(self::LAST_DIGITS, $paymentProfile->getLastDigits());
                $this->assertSame('****' . self::LAST_DIGITS, $paymentProfile->getName());
                $this->assertSame($expectedProfileType, $paymentProfile->getType());
            });
        $manager->expects($this->once())
            ->method('flush');

        $this->doctrineHelper->expects($this->once())
            ->method('getEntityManager')
            ->willReturn($manager);

        $event = new TransactionResponseReceivedEvent($response, $transaction);
        $this->eventListener->onTransactionResponseReceived($event);
    }

    /**
     * @dataProvider notApplicableProvider
     */
    public function testOnTransactionResponseReceivedNotApplicable(array $data, PaymentTransaction $transaction)
    {
        $response = $this->createMock(AuthorizeNetSDKTransactionResponse::class);
        $response->expects($this->once())
            ->method('getData')
            ->willReturn($data);

        $this->requestStack->expects($this->any())
            ->method('getSession')
            ->willReturn($this->createMock(Session::class));

        $customerProfileRepository = $this->createMock(EntityRepository::class);
        $customerProfileRepository->expects($this->never())
            ->method('findOneBy');

        $paymentProfileRepository = $this->createMock(EntityRepository::class);
        $paymentProfileRepository->expects($this->never())
            ->method('findOneBy');

        $event = new TransactionResponseReceivedEvent($response, $transaction);
        $this->eventListener->onTransactionResponseReceived($event);
    }

    public function testOnTransactionResponseReceivedNotApplicableShowError()
    {
        $transaction = $this->createTransaction();
        $response = $this->createMock(AuthorizeNetSDKTransactionResponse::class);

        $response->expects($this->once())
            ->method('getData')
            ->willReturn($this->buildResponseData(false));

        $this->translator->expects($this->once())
            ->method('trans')
            ->willReturn('error_message');
        $flashBag = $this->createMock(FlashBagInterface::class);
        $flashBag->expects($this->once())
            ->method('add')
            ->with('warning', 'error_message');
        $sessionMock = $this->createMock(Session::class);
        $sessionMock->expects($this->once())
            ->method('isStarted')
            ->willReturn(true);
        $requestMock = $this->createMock(Request::class);
        $requestMock->expects($this->exactly(2))
            ->method('getSession')
            ->willReturn($sessionMock);
        $requestMock->expects($this->once())
            ->method('hasSession')
            ->willReturn(true);
        $this->requestStack->expects($this->once())
            ->method('getCurrentRequest')
            ->willReturn($requestMock);
        $sessionMock->expects($this->once())
            ->method('getFlashBag')
            ->willReturn($flashBag);
        $sessionMock->expects($this->once())
            ->method('isStarted')
            ->willReturn(true);
        $sessionMock->expects($this->once())
            ->method('getFlashBag')
            ->willReturn($flashBag);

        $event = new TransactionResponseReceivedEvent($response, $transaction);
        $this->eventListener->onTransactionResponseReceived($event);
    }

    public function notApplicableProvider(): array
    {
        $transactionWithFrontendOwner = $this->createTransaction();

        $noProfileResponseData = $this->buildResponseData(true);
        $successfulResponseData = $noProfileResponseData;
        unset($noProfileResponseData['profile_response']);

        $errorResponseData = $this->buildResponseData(false);

        return [
            'no profile response data' => [
                'data' => $noProfileResponseData,
                'transaction' => $transactionWithFrontendOwner
            ],
            'profile response with error' => [
                'data' => $errorResponseData,
                'transaction' => $transactionWithFrontendOwner
            ],
            'successful response, transaction without frontendOwner' => [
                'data' => $successfulResponseData,
                'transaction' => $this->createTransaction($withFrontendOwner = false)
            ]
        ];
    }

    public function successfulResponseDataProvider(): array
    {
        return [
            'credit card response' => [
                'responseData' => $this->buildResponseData(true, 'visa'),
                'excpectedProfileType' => CustomerPaymentProfile::TYPE_CREDITCARD
            ],
            'echeck response' => [
                'responseData' => $this->buildResponseData(true, 'eCheck'),
                'excpectedProfileType' => CustomerPaymentProfile::TYPE_ECHECK
            ]
        ];
    }

    private function buildResponseData(bool $successful, string $accountType = 'visa'): array
    {
        return [
            'transaction_response' => [
                'account_number' => 'XXXX' . self::LAST_DIGITS,
                'account_type' => $accountType
            ],
            'profile_response' => [
                'customer_profile_id' => self::CUSTOMER_PROFILE_ID,
                'customer_payment_profile_id_list' => [self::PAYMENT_PROFILE_ID],
                'messages' => [
                    'result_code' => $successful ? 'Ok' : 'Error',
                    'message' => [
                        [
                            'code' => $successful ? 'ok_code' : 'error_code',
                            'text' => $successful ? 'Successful.' : 'Error.'
                        ]
                    ]
                ]
            ]
        ];
    }

    private function createTransaction(bool $withFrontendOwner = true): PaymentTransaction
    {
        $transaction = new PaymentTransaction();

        if ($withFrontendOwner) {
            $transaction->setFrontendOwner(new CustomerUser());
        }

        return $transaction;
    }
}
