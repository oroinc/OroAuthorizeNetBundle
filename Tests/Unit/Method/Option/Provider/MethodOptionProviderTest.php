<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\Method\Option\Provider;

use Doctrine\ORM\EntityRepository;
use Oro\Bundle\AuthorizeNetBundle\Entity\CustomerPaymentProfile;
use Oro\Bundle\AuthorizeNetBundle\Entity\CustomerProfile;
use Oro\Bundle\AuthorizeNetBundle\Helper\MerchantCustomerIdGenerator;
use Oro\Bundle\AuthorizeNetBundle\Method\Config\AuthorizeNetConfigInterface;
use Oro\Bundle\AuthorizeNetBundle\Method\Option\Provider\MethodOptionProvider;
use Oro\Bundle\AuthorizeNetBundle\Provider\CustomerProfileProvider;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\OrderBundle\Entity\Order;
use Oro\Bundle\OrderBundle\Entity\OrderLineItem;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;
use Oro\Component\Testing\Unit\EntityTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class MethodOptionProviderTest extends \PHPUnit\Framework\TestCase
{
    use EntityTrait;

    /** @var AuthorizeNetConfigInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $config;

    /** @var PaymentTransaction|\PHPUnit\Framework\MockObject\MockObject */
    private $paymentTransaction;

    /** @var CustomerProfileProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $customerProfileProvider;

    /** @var DoctrineHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $doctrineHelper;

    /** @var MerchantCustomerIdGenerator|\PHPUnit\Framework\MockObject\MockObject */
    private $merchantCustomerIdGenerator;

    /** @var \PHPUnit\Framework\MockObject\MockObject|RequestStack */
    private $requestStack;

    /** @var MethodOptionProvider */
    private $methodOptionProvider;

    public function setUp()
    {
        $this->config = $this->createMock(AuthorizeNetConfigInterface::class);
        $this->paymentTransaction = $this->createMock(PaymentTransaction::class);
        $this->customerProfileProvider = $this->createMock(CustomerProfileProvider::class);
        $this->doctrineHelper = $this->createMock(DoctrineHelper::class);
        $this->merchantCustomerIdGenerator = $this->createMock(MerchantCustomerIdGenerator::class);
        $this->requestStack = $this->createMock(RequestStack::class);

        $this->methodOptionProvider = new MethodOptionProvider(
            $this->config,
            $this->paymentTransaction,
            $this->customerProfileProvider,
            $this->doctrineHelper,
            $this->merchantCustomerIdGenerator,
            $this->requestStack
        );
    }

    /**
     * @param array $additionalData
     */
    private function setupTransactionAdditionalData(array $additionalData): void
    {
        $sourceTransaction = $this->createMock(PaymentTransaction::class);
        $options = ['additionalData' => \json_encode($additionalData)];
        $sourceTransaction
            ->method('getTransactionOptions')
            ->willReturn($options)
        ;
        $this->paymentTransaction
            ->method('getSourcePaymentTransaction')
            ->willReturn($sourceTransaction)
        ;
    }

    public function testGetSolutionIdLiveMode()
    {
        $this->config
            ->expects($this->once())
            ->method('isTestMode')
            ->willReturn(false);

        $this->assertEquals(MethodOptionProvider::SOLUTION_ID, $this->methodOptionProvider->getSolutionId());
    }

    public function testGetSolutionIdTestMode()
    {
        $this->config
            ->expects($this->once())
            ->method('isTestMode')
            ->willReturn(true);

        $this->assertNull($this->methodOptionProvider->getSolutionId());
    }

    public function testGetApiLoginId()
    {
        $expectedApiLoginId = 'API_LOGIN_ID';
        $this->config
            ->expects($this->once())
            ->method('getApiLoginId')
            ->willReturn($expectedApiLoginId);

        $this->assertEquals($expectedApiLoginId, $this->methodOptionProvider->getApiLoginId());
    }

    public function testGetTransactionKey()
    {
        $expectedTransactionKey = 'TRANS_KEY';
        $this->config
            ->expects($this->once())
            ->method('getTransactionKey')
            ->willReturn($expectedTransactionKey);

        $this->assertEquals($expectedTransactionKey, $this->methodOptionProvider->getTransactionKey());
    }

    /**
     * @return array
     */
    public function opaqueDataProvider(): array
    {
        return [
            'exceptiondataDescriptorNotFound' => [
                \LogicException::class,
                sprintf('Can not find field "%s" in additional data', 'dataDescriptor'),
                ['dataValue' => 'someValue']
            ],
            'exceptionDataValueNotFound' => [
                \LogicException::class,
                sprintf('Can not find field "%s" in additional data', 'dataValue'),
                ['dataDescriptor' => 'someDescriptor']
            ],
            'opaqueDataOk' => [
                null,
                null,
                ['dataDescriptor' => 'someDescriptor', 'dataValue' => 'someValue']
            ]
        ];
    }

    /**
     * @dataProvider opaqueDataProvider
     * @param null|string $exceptionClass
     * @param null|string $exceptionMessage
     * @param array $additionalData
     */
    public function testOpaqueOptions(
        ?string $exceptionClass,
        ?string $exceptionMessage,
        array $additionalData
    ) {
        $this->setupTransactionAdditionalData($additionalData);
        if (null !== $exceptionClass) {
            $this->expectException($exceptionClass);
        }

        if (null !== $exceptionMessage) {
            $this->expectExceptionMessage($exceptionMessage);
        }

        $actualDescriptor = $this->methodOptionProvider->getDataDescriptor();
        $actualValue = $this->methodOptionProvider->getDataValue();

        $this->assertEquals($additionalData['dataDescriptor'], $actualDescriptor);
        $this->assertEquals($additionalData['dataValue'], $actualValue);
    }

    public function testCustomerExistsOptions()
    {
        $frontendOwner = $this->getEntity(CustomerUser::class, ['id' => 77, 'email' => 'test@ggmail.com']);
        $this->paymentTransaction->method('getFrontendOwner')->willReturn($frontendOwner);
        $this->customerProfileProvider->method('findCustomerProfile')->willReturn(
            $this->getEntity(
                CustomerProfile::class,
                ['customerProfileId' => 'x1y2z3']
            )
        );
        $this->setupTransactionAdditionalData(['profileId' => 1]);
        $repositoryMock = $this->createMock(EntityRepository::class);
        $customerProfile = $this->getEntity(CustomerProfile::class, ['customerUser' => $frontendOwner]);
        $repositoryMock->method('find')->with(1)->willReturn(
            $this->getEntity(
                CustomerPaymentProfile::class,
                [
                    'customerPaymentProfileId' => 'zzz888',
                    'customerProfile' => $customerProfile,
                    'customerUser' => $frontendOwner
                ]
            )
        );
        $this->doctrineHelper->method('getEntityRepository')->willReturn($repositoryMock);

        $actualIsCustomerProfileExists = $this->methodOptionProvider->isCustomerProfileExists();
        $actualExistingCustomerProfileId = $this->methodOptionProvider->getExistingCustomerProfileId();
        $actualExistingPaymentProfileId = $this->methodOptionProvider->getExistingCustomerPaymentProfileId();

        $this->assertTrue($actualIsCustomerProfileExists);
        $this->assertEquals($actualExistingCustomerProfileId, 'x1y2z3');
        $this->assertEquals($actualExistingPaymentProfileId, 'zzz888');
    }

    public function testCustomerNotExistsException()
    {
        $this->setupTransactionAdditionalData([]);
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('profileId is required');
        $this->methodOptionProvider->getExistingCustomerPaymentProfileId();
    }

    public function testCustomerProfileNotFoundException()
    {
        $this->setupTransactionAdditionalData(['profileId' => 5]);
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Can not find customer payment profile with id #5');

        $repositoryMock = $this->createMock(EntityRepository::class);
        $repositoryMock->method('find')->with(5)->willReturn(null);
        $this->doctrineHelper->method('getEntityRepository')->willReturn($repositoryMock);
        $this->methodOptionProvider->getExistingCustomerPaymentProfileId();
    }

    public function testProfileIdAbsentException()
    {
        $this->customerProfileProvider->method('findCustomerProfile')->willReturn(null);
        $repositoryMock = $this->createMock(EntityRepository::class);
        $repositoryMock->method('find')->with(1)->willReturn(null);
        $this->doctrineHelper->method('getEntityRepository')->willReturn($repositoryMock);

        $actualIsCustomerProfileExists = $this->methodOptionProvider->isCustomerProfileExists();
        $this->assertFalse($actualIsCustomerProfileExists);
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Customer profile not exists');
        $this->methodOptionProvider->getExistingCustomerProfileId();
    }

    public function testAccessDeniedException()
    {
        $frontendOwner = $this->getEntity(CustomerUser::class, ['id' => 77]);
        $this->paymentTransaction->method('getFrontendOwner')->willReturn($frontendOwner);
        $this->setupTransactionAdditionalData(['profileId' => 5]);
        $this->expectException(AccessDeniedException::class);
        $this->expectExceptionMessage('Access to customer profile denied');

        $repositoryMock = $this->createMock(EntityRepository::class);
        $otherFrontendUser = $this->getEntity(CustomerUser::class, ['id' => 88]);
        $customerProfile = $this->getEntity(
            CustomerProfile::class,
            ['customerUser' => $otherFrontendUser]
        );
        $repositoryMock->method('find')->with(5)->willReturn(
            $this->getEntity(
                CustomerPaymentProfile::class,
                [
                    'customerPaymentProfileId' => 'zzz888',
                    'customerProfile' => $customerProfile,
                    'customerUser' => $otherFrontendUser
                ]
            )
        );
        $this->doctrineHelper->method('getEntityRepository')->willReturn($repositoryMock);
        $this->methodOptionProvider->getExistingCustomerPaymentProfileId();
    }

    public function testNewCustomerProfileIdExistsException()
    {
        $this->customerProfileProvider->method('findCustomerProfile')->willReturn(
            $this->getEntity(
                CustomerProfile::class,
                ['customerProfileId' => 'x1y2z3']
            )
        );

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Customer profile already exists');

        $this->methodOptionProvider->getGeneratedNewCustomerProfileId();
    }

    public function testNewCustomerProfileIdFronendOwnerAbsentException()
    {
        $this->customerProfileProvider->method('findCustomerProfile')->willReturn(null);
        $this->paymentTransaction->method('getFrontendOwner')->willReturn(null);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Customer User not defined');

        $this->methodOptionProvider->getGeneratedNewCustomerProfileId();
    }

    public function testNewCustomerProfileIdGenerateOk()
    {
        $this->customerProfileProvider->method('findCustomerProfile')->willReturn(null);
        $frontendOwner = $this->getEntity(CustomerUser::class, ['id' => 77, 'email' => 'test@ggmail.com']);
        $this->paymentTransaction->method('getFrontendOwner')->willReturn($frontendOwner);
        $this->config->method('getIntegrationId')->willReturn(101);
        $this->merchantCustomerIdGenerator->expects($this->once())->method('generate')->willReturnCallback(
            function (int $integrationId, int $frontendOwnerId) {
                return sprintf('oro-%d-%d', $integrationId, $frontendOwnerId);
            }
        );

        $generatedId = $this->methodOptionProvider->getGeneratedNewCustomerProfileId();
        $actualEmail = $this->methodOptionProvider->getEmail();
        $this->assertEquals('oro-101-77', $generatedId);
        $this->assertEquals('test@ggmail.com', $actualEmail);
    }

    public function testOtherOptions()
    {
        $this->setupTransactionAdditionalData(
            [
                'profileId' => 5,
                'cvv' => 123,
                'saveProfile' => true
            ]
        );
        $this->assertEquals(5, $this->methodOptionProvider->getProfileId());
        $this->assertEquals(123, $this->methodOptionProvider->getCardCode());
        $this->assertEquals(true, $this->methodOptionProvider->getCreateProfile());

        $this->paymentTransaction->expects($this->once())->method('getAmount')->willReturn(10);
        $this->paymentTransaction->expects($this->once())->method('getCurrency')->willReturn('USD');
        $this->paymentTransaction->expects($this->once())->method('getReference')->willReturn('x1y2');
        $this->assertEquals(10, $this->methodOptionProvider->getAmount());
        $this->assertEquals('USD', $this->methodOptionProvider->getCurrency());
        $this->assertEquals('x1y2', $this->methodOptionProvider->getOriginalTransaction());
    }

    public function testGetInvoiceNumber()
    {
        $order = new Order();
        $order->setIdentifier('ORDER-IDENTIFIER');

        $this->doctrineHelper
            ->expects($this->once())
            ->method('getEntityReference')
            ->willReturn($order);

        $this->assertEquals($order->getIdentifier(), $this->methodOptionProvider->getInvoiceNumber());
    }

    public function testGetInvoiceNumberNotOrderEntity()
    {
        $this->doctrineHelper
            ->expects($this->once())
            ->method('getEntityReference')
            ->willReturn(new \stdClass());

        $this->assertNull($this->methodOptionProvider->getInvoiceNumber());
    }

    public function testGetLineItems()
    {
        $order = new Order();
        $order->addLineItem(new OrderLineItem());
        $order->addLineItem(new OrderLineItem());

        $this->doctrineHelper
            ->expects($this->once())
            ->method('getEntityReference')
            ->willReturn($order);

        $this->assertEquals($order->getLineItems()->toArray(), $this->methodOptionProvider->getLineItems());
    }

    public function testGetLineItemsNotOrderEntity()
    {
        $this->doctrineHelper
            ->expects($this->once())
            ->method('getEntityReference')
            ->willReturn(new \stdClass());

        $this->assertNull($this->methodOptionProvider->getLineItems());
    }

    /**
     * @dataProvider isCIMEnabledProvider
     * @param bool $configEnabled
     */
    public function testIsCIMEnabled($configEnabled)
    {
        $this->config
            ->expects($this->once())
            ->method('isEnabledCIM')
            ->willReturn($configEnabled);

        $this->assertEquals($configEnabled, $this->methodOptionProvider->isCIMEnabled());
    }

    /**
     * @return array
     */
    public function isCIMEnabledProvider()
    {
        return [
            'cim enabled' => [
                'configEnabled' => true
            ],
            'cim disabled' => [
                'configEnabled' => true
            ]
        ];
    }

    public function testGetCustomerIpOptions()
    {
        $request = $this->createMock(Request::class);
        $request->expects($this->once())
            ->method('getClientIp')
            ->willReturn('127.0.0.1');

        $this->requestStack->expects($this->once())
            ->method('getCurrentRequest')
            ->willReturn($request);

        $this->assertEquals('127.0.0.1', $this->methodOptionProvider->getClientIp());
    }

    public function testGetCustomerIpOptionsWithoutRequest()
    {
        $this->requestStack->expects($this->once())
            ->method('getCurrentRequest')
            ->willReturn(null);

        $this->assertNull($this->methodOptionProvider->getClientIp());
    }
}
