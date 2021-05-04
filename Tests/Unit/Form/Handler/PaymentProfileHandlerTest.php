<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\Form\Handler;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Oro\Bundle\AuthorizeNetBundle\Entity\CustomerPaymentProfile;
use Oro\Bundle\AuthorizeNetBundle\Entity\CustomerProfile;
use Oro\Bundle\AuthorizeNetBundle\Form\Handler\PaymentProfileHandler;
use Oro\Bundle\AuthorizeNetBundle\Helper\RequestSender;
use Oro\Bundle\AuthorizeNetBundle\Model\DTO\PaymentProfileAddressDTO;
use Oro\Bundle\AuthorizeNetBundle\Model\DTO\PaymentProfileDTO;
use Oro\Bundle\AuthorizeNetBundle\Model\DTO\PaymentProfileMaskedDataDTO;
use Oro\Bundle\AuthorizeNetBundle\Provider\CustomerProfileProvider;
use Oro\Bundle\AuthorizeNetBundle\Provider\IntegrationProvider;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessor;
use Oro\Component\Testing\Unit\EntityTrait;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Contracts\Translation\TranslatorInterface;

class PaymentProfileHandlerTest extends \PHPUnit\Framework\TestCase
{
    use EntityTrait;

    /** @var IntegrationProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $integrationProvider;

    /** @var RequestSender|\PHPUnit\Framework\MockObject\MockObject */
    private $requestSender;

    /** @var TokenAccessor|\PHPUnit\Framework\MockObject\MockObject */
    private $tokenAccessor;

    /** @var DoctrineHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $doctrineHelper;

    /** @var EntityRepository|\PHPUnit\Framework\MockObject\MockObject */
    private $repository;

    /** @var EntityManager|\PHPUnit\Framework\MockObject\MockObject */
    private $manager;

    /** @var FormInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $form;

    /** @var Session|\PHPUnit\Framework\MockObject\MockObject */
    private $session;

    /** @var CustomerProfileProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $customerProfileProvider;

    /** @var PaymentProfileHandler */
    private $handler;

    protected function setUp(): void
    {
        $this->doctrineHelper = $this->createMock(DoctrineHelper::class);
        $this->integrationProvider = $this->createMock(IntegrationProvider::class);
        $this->tokenAccessor = $this->createMock(TokenAccessor::class);
        $this->repository = $this->createMock(EntityRepository::class);
        $this->requestSender = $this->createMock(RequestSender::class);
        $this->manager = $this->createMock(EntityManager::class);
        $this->form = $this->createMock(FormInterface::class);
        $this->session = $this->createMock(Session::class);

        $this->customerProfileProvider = $this->createMock(CustomerProfileProvider::class);
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $translator = $this->createMock(TranslatorInterface::class);

        $this->handler = new PaymentProfileHandler(
            $eventDispatcher,
            $this->doctrineHelper,
            $this->tokenAccessor,
            $this->session,
            $this->requestSender,
            $translator,
            $this->integrationProvider,
            $this->customerProfileProvider
        );
    }

    public function testProcessCreatePaymentProfileOnly()
    {
        $customerUser = new CustomerUser();
        $paymentProfileDTO = new PaymentProfileDTO();
        $paymentProfile = $paymentProfileDTO->getProfile();
        $customerProfile = new CustomerProfile();

        $this->customerProfileProvider
            ->expects($this->once())
            ->method('findCustomerProfile')
            ->with($customerUser)
            ->willReturn($customerProfile);

        $this->tokenAccessor
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($customerUser);

        $this->manager
            ->expects($this->once())
            ->method('persist')
            ->with($paymentProfile);

        $this->manager
            ->expects($this->once())
            ->method('flush');

        $this->doctrineHelper
            ->method('getEntityManager')
            ->withAnyParameters()
            ->willReturn($this->manager);

        $this->form
            ->expects($this->once())
            ->method('isSubmitted')
            ->willReturn(true);

        $this->form
            ->expects($this->once())
            ->method('isValid')
            ->willReturn(true);

        $this->requestSender
            ->expects($this->once())
            ->method('createCustomerPaymentProfile')
            ->with($paymentProfileDTO)
            ->willReturn('NEW');

        $result = $this->handler->process($paymentProfileDTO, $this->form, new Request());
        $this->assertTrue($result);
        $this->assertEquals('NEW', $paymentProfile->getCustomerPaymentProfileId());
        $this->assertEquals($customerProfile, $paymentProfile->getCustomerProfile());
    }

    public function testProcessCreateCustomerProfileAndPaymentProfile()
    {
        $customerUser = new CustomerUser();
        $integration = new Channel();
        $paymentProfileDTO = new PaymentProfileDTO();
        $paymentProfile = $paymentProfileDTO->getProfile();
        $customerProfileId = 'NEW_CUSTOMER_PROFILE_ID';

        $this->tokenAccessor
            ->expects($this->exactly(2))
            ->method('getUser')
            ->willReturn($customerUser);

        $this->integrationProvider
            ->expects($this->once())
            ->method('getIntegration')
            ->willReturn($integration);

        $this->manager
            ->expects($this->exactly(2))
            ->method('persist');

        $this->manager
            ->expects($this->exactly(2))
            ->method('flush');

        $this->doctrineHelper
            ->expects($this->exactly(2))
            ->method('getEntityManager')
            ->withAnyParameters()
            ->willReturn($this->manager);

        $this->form
            ->expects($this->once())
            ->method('isSubmitted')
            ->willReturn(true);

        $this->form
            ->expects($this->once())
            ->method('isValid')
            ->willReturn(true);

        $this->requestSender
            ->expects($this->once())
            ->method('createCustomerProfile')
            ->willReturn($customerProfileId);

        $this->requestSender
            ->expects($this->once())
            ->method('createCustomerPaymentProfile')
            ->with($paymentProfileDTO)
            ->willReturn('NEW');

        $result = $this->handler->process($paymentProfileDTO, $this->form, new Request());
        $this->assertTrue($result);
        $this->assertEquals('NEW', $paymentProfile->getCustomerPaymentProfileId());
        $customerProfile = $paymentProfile->getCustomerProfile();
        $this->assertNotNull($customerProfile);
        $this->assertEquals($customerProfileId, $customerProfile->getCustomerProfileId());
    }

    public function testProcessUpdatePaymentProfile()
    {
        $customerUser = new CustomerUser();
        $paymentProfileDTO = new PaymentProfileDTO();
        $paymentProfile = $paymentProfileDTO->getProfile();
        $paymentProfile->setCustomerProfile(new CustomerProfile());
        $paymentProfile->setCustomerPaymentProfileId('UPDATED');

        $this->customerProfileProvider
            ->expects($this->never())
            ->method('findCustomerProfile');

        $this->tokenAccessor
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($customerUser);

        $this->manager
            ->expects($this->once())
            ->method('persist')
            ->with($paymentProfile);

        $this->manager
            ->expects($this->once())
            ->method('flush');

        $this->doctrineHelper
            ->method('getEntityManager')
            ->withAnyParameters()
            ->willReturn($this->manager);

        $this->form
            ->expects($this->once())
            ->method('isSubmitted')
            ->willReturn(true);

        $this->form
            ->expects($this->once())
            ->method('isValid')
            ->willReturn(true);

        $this->requestSender
            ->expects($this->once())
            ->method('updateCustomerPaymentProfile')
            ->with($paymentProfileDTO)
            ->willReturn(true);

        $result = $this->handler->process($paymentProfileDTO, $this->form, new Request());
        $this->assertTrue($result);
    }

    public function testProcessCreatePaymentProfileWithApiErrorCustomerProfileSaved()
    {
        $customerUser = new CustomerUser();
        $integration = new Channel();
        $paymentProfileDTO = new PaymentProfileDTO();
        $customerProfileId = 'NEW_CUSTOMER_PROFILE_ID';

        $this->tokenAccessor
            ->expects($this->exactly(2))
            ->method('getUser')
            ->willReturn($customerUser);

        $this->integrationProvider
            ->expects($this->once())
            ->method('getIntegration')
            ->willReturn($integration);

        $customerProfileCallback = function (CustomerProfile $customerProfile) use ($customerProfileId) {
            $this->assertEquals($customerProfileId, $customerProfile->getCustomerProfileId());
        };

        $this->manager
            ->expects($this->once())
            ->method('persist')
            ->willReturnCallback($customerProfileCallback);

        $this->manager
            ->expects($this->once())
            ->method('flush')
            ->willReturnCallback($customerProfileCallback);

        $this->doctrineHelper
            ->method('getEntityManager')
            ->withAnyParameters()
            ->willReturn($this->manager);

        $this->form
            ->expects($this->once())
            ->method('isSubmitted')
            ->willReturn(true);

        $this->form
            ->expects($this->once())
            ->method('isValid')
            ->willReturn(true);

        $this->requestSender
            ->expects($this->once())
            ->method('createCustomerProfile')
            ->willReturn($customerProfileId);

        $this->requestSender
            ->expects($this->once())
            ->method('createCustomerPaymentProfile')
            ->with($paymentProfileDTO)
            ->willThrowException(new \LogicException('api error'));

        $this->session
            ->method('getFlashBag')
            ->willReturn(new FlashBag());

        $result = $this->handler->process($paymentProfileDTO, $this->form, new Request());
        $this->assertFalse($result);
        $customerProfile = $paymentProfileDTO->getProfile()->getCustomerProfile();
        $this->assertNotNull($customerProfile);
        $this->assertEquals($customerProfileId, $customerProfile->getCustomerProfileId());
    }

    public function testProcessCreateCustomerProfileWithApiError()
    {
        $customerUser = new CustomerUser();
        $integration = new Channel();
        $paymentProfileDTO = new PaymentProfileDTO();

        $this->tokenAccessor
            ->expects($this->exactly(2))
            ->method('getUser')
            ->willReturn($customerUser);

        $this->integrationProvider
            ->expects($this->once())
            ->method('getIntegration')
            ->willReturn($integration);

        $this->manager
            ->expects($this->never())
            ->method('persist');

        $this->manager
            ->expects($this->never())
            ->method('flush');

        $this->doctrineHelper
            ->method('getEntityManager')
            ->withAnyParameters()
            ->willReturn($this->manager);

        $this->form
            ->expects($this->once())
            ->method('isSubmitted')
            ->willReturn(true);

        $this->form
            ->expects($this->once())
            ->method('isValid')
            ->willReturn(true);

        $this->requestSender
            ->expects($this->once())
            ->method('createCustomerProfile')
            ->willThrowException(new \LogicException('api error'));

        $this->session
            ->method('getFlashBag')
            ->willReturn(new FlashBag());

        $result = $this->handler->process($paymentProfileDTO, $this->form, new Request());
        $this->assertFalse($result);
        $this->assertNull($paymentProfileDTO->getProfile()->getCustomerProfile());
    }

    public function testProcessUpdatePaymentProfileWithApiError()
    {
        $customerUser = new CustomerUser();
        $paymentProfileDTO = new PaymentProfileDTO();
        $paymentProfile = $paymentProfileDTO->getProfile();
        $paymentProfile->setCustomerProfile(new CustomerProfile());
        $paymentProfile->setCustomerPaymentProfileId('UPDATED');

        $this->tokenAccessor
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($customerUser);

        $this->manager
            ->expects($this->never())
            ->method('persist');

        $this->manager
            ->expects($this->never())
            ->method('flush');

        $this->doctrineHelper
            ->method('getEntityManager')
            ->withAnyParameters()
            ->willReturn($this->manager);

        $this->form
            ->expects($this->once())
            ->method('isSubmitted')
            ->willReturn(true);

        $this->form
            ->expects($this->once())
            ->method('isValid')
            ->willReturn(true);

        $this->requestSender
            ->expects($this->once())
            ->method('updateCustomerPaymentProfile')
            ->with($paymentProfileDTO)
            ->willThrowException(new \LogicException('api error'));

        $this->session
            ->method('getFlashBag')
            ->willReturn(new FlashBag());

        $result = $this->handler->process($paymentProfileDTO, $this->form, new Request());
        $this->assertFalse($result);
    }

    public function testProcessNewPaymentProfileFormNotSubmited()
    {
        $paymentProfileDTO = new PaymentProfileDTO();

        $this->form
            ->expects($this->exactly(2))
            ->method('isSubmitted')
            ->willReturn(false);

        $result = $this->handler->process($paymentProfileDTO, $this->form, new Request());
        $this->assertFalse($result);
    }

    public function testProcessNewPaymentProfileFormIsNotValid()
    {
        $paymentProfileDTO = new PaymentProfileDTO();

        $this->form
            ->expects($this->exactly(2))
            ->method('isSubmitted')
            ->willReturn(true);

        $this->form
            ->expects($this->once())
            ->method('isValid')
            ->willReturn(false);

        $result = $this->handler->process($paymentProfileDTO, $this->form, new Request());
        $this->assertFalse($result);
    }

    public function testProcessFillUpAddressAndMaskedDataFromApi()
    {
        $paymentProfile = $this->getEntity(CustomerPaymentProfile::class, ['id' => 1]);
        $paymentProfileDTO = new PaymentProfileDTO($paymentProfile);

        $this->form
            ->expects($this->exactly(2))
            ->method('isSubmitted')
            ->willReturn(false);

        $paymentProfileData = [
            'bill_to' => [
                'first_name' => 'from_api'
            ],
            'payment' => [
                'bank_account' => [
                    'account_number' => 'XXXX1234',
                    'routing_number' => 'XXXX4321',
                    'name_on_account' => 'first last',
                    'account_type' => 'account type',
                    'bank_name' => 'bank name'
                ]
            ]
        ];

        $addressDTOFromApi = new PaymentProfileAddressDTO();
        $addressDTOFromApi->setFirstName('from api');

        $maskedDataDTOFromApi = new PaymentProfileMaskedDataDTO();
        $maskedDataDTOFromApi->setAccountNumber('XXXX1234');
        $maskedDataDTOFromApi->setRoutingNumber('XXXX4321');
        $maskedDataDTOFromApi->setNameOnAccount('first last');
        $maskedDataDTOFromApi->setAccountType('account type');
        $maskedDataDTOFromApi->setBankName('bank name');

        $this->requestSender
            ->expects($this->once())
            ->method('getCustomerPaymentProfile')
            ->with($paymentProfile)
            ->willReturn($paymentProfileData);

        $this->requestSender
            ->expects($this->once())
            ->method('getPaymentProfileMaskedDataDTO')
            ->willReturn($maskedDataDTOFromApi);

        $this->requestSender
            ->expects($this->once())
            ->method('getPaymentProfileAddressDTO')
            ->with($paymentProfileData['bill_to'])
            ->willReturn($addressDTOFromApi);

        $this->form
            ->expects($this->once())
            ->method('setData')
            ->with($paymentProfileDTO);

        $this->handler->process($paymentProfileDTO, $this->form, new Request());
        $this->assertEquals($addressDTOFromApi, $paymentProfileDTO->getAddress());
    }

    public function testProcessNotApplicableData()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->handler->process(new \stdClass(), $this->form, new Request());
    }
}
