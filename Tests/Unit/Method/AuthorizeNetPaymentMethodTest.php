<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\Method;

use Doctrine\ORM\EntityRepository;
use JMS\Serializer\ArrayTransformerInterface;
use net\authorize\api\contract\v1\CreateTransactionResponse;
use net\authorize\api\contract\v1\MessagesType;
use net\authorize\api\contract\v1\TransactionResponseType;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Gateway;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option\CreateProfile;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option\Transaction;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Response\AuthorizeNetSDKTransactionResponse;
use Oro\Bundle\AuthorizeNetBundle\Entity\CustomerPaymentProfile;
use Oro\Bundle\AuthorizeNetBundle\Entity\CustomerProfile;
use Oro\Bundle\AuthorizeNetBundle\Event\TransactionResponseReceivedEvent;
use Oro\Bundle\AuthorizeNetBundle\Helper\MerchantCustomerIdGenerator;
use Oro\Bundle\AuthorizeNetBundle\Method\AuthorizeNetPaymentMethod;
use Oro\Bundle\AuthorizeNetBundle\Method\Config\AuthorizeNetConfigInterface;
use Oro\Bundle\AuthorizeNetBundle\Method\Option\Provider\Factory\MethodOptionProviderFactory;
use Oro\Bundle\AuthorizeNetBundle\Method\Option\Resolver\MethodOptionResolver;
use Oro\Bundle\AuthorizeNetBundle\Provider\CustomerProfileProvider;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\PaymentBundle\Context\PaymentContextInterface;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;
use Oro\Bundle\PaymentBundle\Method\PaymentMethodInterface;
use Oro\Bundle\PaymentBundle\Provider\AddressExtractor;
use Oro\Bundle\TaxBundle\Model\Result;
use Oro\Bundle\TaxBundle\Provider\TaxProviderInterface;
use Oro\Bundle\TaxBundle\Provider\TaxProviderRegistry;
use Oro\Component\Testing\Unit\EntityTrait;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class AuthorizeNetPaymentMethodTest extends \PHPUnit\Framework\TestCase
{
    use EntityTrait;

    private const INTEGRATION_ID = 4;
    private const CUSTOMER_USER_ID = 77;

    /** @var Gateway|\PHPUnit\Framework\MockObject\MockObject */
    protected $gateway;

    /** @var AuthorizeNetPaymentMethod */
    protected $method;

    /** @var AuthorizeNetConfigInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $paymentConfig;

    /** @var ArrayTransformerInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $serializer;

    /** @var RequestStack|\PHPUnit\Framework\MockObject\MockObject */
    protected $requestStack;

    /** @var CustomerProfileProvider|\PHPUnit\Framework\MockObject\MockObject */
    protected $customerProfileProvider;

    /** @var CustomerUser|\PHPUnit\Framework\MockObject\MockObject */
    protected $frontendOwner;

    /** @var DoctrineHelper|\PHPUnit\Framework\MockObject\MockObject */
    protected $doctrineHelper;

    /** @var AddressExtractor|\PHPUnit\Framework\MockObject\MockObject */
    protected $addressExtractor;

    /** @var EventDispatcherInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $eventDispatcher;

    /** @var TaxProviderRegistry */
    private $taxProviderRegistry;

    protected function setUp(): void
    {
        $this->gateway = $this->createMock(Gateway::class);
        $this->paymentConfig = $this->createMock(AuthorizeNetConfigInterface::class);
        $this->paymentConfig->expects($this->any())
            ->method('getIntegrationId')
            ->willReturn(self::INTEGRATION_ID);
        $this->requestStack = $this->createMock(RequestStack::class);
        $this->customerProfileProvider = $this->createMock(
            CustomerProfileProvider::class
        );
        $this->doctrineHelper = $this->createMock(DoctrineHelper::class);
        $this->addressExtractor = $this->createMock(AddressExtractor::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->taxProviderRegistry = $this->createMock(TaxProviderRegistry::class);

        $this->method = new AuthorizeNetPaymentMethod(
            $this->gateway,
            $this->paymentConfig,
            $this->requestStack,
            new MethodOptionResolver(
                new MethodOptionProviderFactory(
                    $this->customerProfileProvider,
                    new MerchantCustomerIdGenerator(),
                    $this->doctrineHelper,
                    $this->addressExtractor,
                    $this->taxProviderRegistry,
                    $this->requestStack
                )
            ),
            $this->eventDispatcher
        );

        $this->serializer = $this->createMock(ArrayTransformerInterface::class);

        $this->frontendOwner = $this->createMock(CustomerUser::class);
        $this->frontendOwner->expects($this->any())->method('getId')->willReturn(self::CUSTOMER_USER_ID);
    }

    /**
     * @param string $customerProfileId
     * @param CustomerUser $customerUser
     *
     * @return CustomerProfile
     */
    protected function createCustomerProfile(string $customerProfileId, CustomerUser $customerUser)
    {
        /** @var CustomerProfile $customerProfile */
        $customerProfile = $this->getEntity(CustomerProfile::class, [
            'customerProfileId' => $customerProfileId,
            'customerUser' => $customerUser
        ]);

        $this->customerProfileProvider->expects($this->once())
            ->method('findCustomerProfile')
            ->with($customerUser)
            ->willReturn($customerProfile)
        ;

        return $customerProfile;
    }

    /**
     * @param int $oroId
     * @param string $remoteId
     * @param CustomerProfile $customerProfile
     *
     * @return CustomerProfile
     */
    protected function createCustomerPaymentProfile(int $oroId, string $remoteId, CustomerProfile $customerProfile)
    {
        /** @var CustomerPaymentProfile $customerPaymentProfile */
        $customerPaymentProfile = $this->getEntity(CustomerPaymentProfile::class, [
            'id' => $oroId,
            'customerPaymentProfileId' => $remoteId,
            'customerProfile' => $customerProfile
        ]);

        $repositoryMock = $this->createMock(EntityRepository::class);
        $repositoryMock->expects($this->any())
            ->method('find')
            ->with($oroId)
            ->willReturn($customerPaymentProfile)
        ;
        $this->doctrineHelper
            ->expects($this->any())
            ->method('getEntityRepository')
            ->with(CustomerPaymentProfile::class)
            ->willReturn($repositoryMock)
        ;

        return $customerPaymentProfile;
    }

    /**
     * @dataProvider purchaseExecuteProvider
     * @param string $purchaseAction
     * @param string $gatewayTransactionType
     * @param bool $requestSuccessful
     * @param string $expectedMessage
     * @param string|null $transId
     * @param array $responseArray
     * @param int|null $profileId
     */
    public function testPurchaseExecute(
        $purchaseAction,
        $gatewayTransactionType,
        $requestSuccessful,
        $expectedMessage,
        $transId,
        array $responseArray,
        int $profileId = null
    ) {
        $testMode = false;
        $transaction = $this->createPaymentTransaction(PaymentMethodInterface::PURCHASE, $profileId);

        $request = $this->createMock(Request::class);
        $request->expects($this->any())
            ->method('getClientIp')
            ->willReturn('127.0.0.1');
        $this->requestStack->expects($this->any())
            ->method('getCurrentRequest')
            ->willReturn($request);

        $this->paymentConfig->expects($this->any())
            ->method('isTestMode')
            ->willReturn($testMode);

        $this->paymentConfig->expects($this->any())
            ->method('getPurchaseAction')
            ->willReturn($purchaseAction);

        $this->paymentConfig->expects($this->any())
            ->method('getApiLoginId')
            ->willReturn('API_LOGIN_ID');

        $this->paymentConfig->expects($this->any())
            ->method('getTransactionKey')
            ->willReturn('API_TRANSACTION_KEY');

        $this->paymentConfig->expects($this->any())
            ->method('isEnabledCIM')
            ->willReturn(true);

        $this->gateway->expects($this->once())
            ->method('setTestMode')
            ->with($testMode);

        $response = $this->prepareSDKResponse($requestSuccessful);

        $this->gateway->expects($this->once())
            ->method('request')
            ->with($gatewayTransactionType)
            ->willReturn($response);

        $event = new TransactionResponseReceivedEvent($response, $transaction);
        $this->eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with(TransactionResponseReceivedEvent::NAME, $event);

        $taxProvider = $this->createMock(TaxProviderInterface::class);
        $taxProvider->expects($this->once())
            ->method('getTax')
            ->willReturn(Result::jsonDeserialize(null));

        $this->taxProviderRegistry
            ->expects($this->once())
            ->method('getEnabledProvider')
            ->willReturn($taxProvider);

        if (null !== $profileId) {
            $customerProfile = $this->createCustomerProfile('x-y-z', $this->frontendOwner);
            $this->createCustomerPaymentProfile($profileId, 'p-x-y-z-', $customerProfile);
        }

        $this->assertEquals(
            [
                'message' => $expectedMessage,
                'successful' => $requestSuccessful,
            ],
            $this->method->execute($transaction->getAction(), $transaction)
        );

        $this->assertSame($requestSuccessful, $transaction->isSuccessful());
        $this->assertSame($requestSuccessful, $transaction->isActive());
        $this->assertSame($transId, $transaction->getReference());
        $this->assertSame($responseArray, $transaction->getResponse());
        $options = $transaction->getRequest();
        $this->assertArrayHasKey('customer_ip', $options);
        $this->assertEquals('127.0.0.1', $options['customer_ip']);
    }

    public function testExecuteException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported action "wrong_action"');

        $transaction = new PaymentTransaction();
        $transaction->setAction('wrong_action');

        $this->method->execute($transaction->getAction(), $transaction);
    }


    /**
     * @dataProvider executeProvider
     * @param string $paymentAction
     * @param string $gatewayTransactionType
     * @param bool $requestSuccessful
     * @param string $expectedMessage
     * @param string|null $transId
     * @param array $responseArray
     */
    public function testExecute(
        $paymentAction,
        $gatewayTransactionType,
        $requestSuccessful,
        $expectedMessage,
        $transId,
        array $responseArray
    ) {
        $testMode = false;
        $transaction = $this->createPaymentTransaction($paymentAction);

        $this->paymentConfig->expects($this->any())
            ->method('isTestMode')
            ->willReturn($testMode);

        $this->paymentConfig->expects($this->any())
            ->method('getApiLoginId')
            ->willReturn('API_LOGIN_ID');

        $this->paymentConfig->expects($this->any())
            ->method('getTransactionKey')
            ->willReturn('API_TRANSACTION_KEY');

        $this->gateway->expects($this->once())
            ->method('setTestMode')
            ->with($testMode);

        $response = $this->prepareSDKResponse($requestSuccessful);

        $this->gateway->expects($this->once())
            ->method('request')
            ->with($gatewayTransactionType)
            ->willReturn($response);

        $this->assertEquals(
            [
                'message' => $expectedMessage,
                'successful' => $requestSuccessful,
            ],
            $this->method->execute($transaction->getAction(), $transaction)
        );

        $this->assertSame($requestSuccessful, $transaction->isSuccessful());
        $this->assertSame($requestSuccessful, $transaction->isActive());
        $this->assertSame($transId, $transaction->getReference());
        $this->assertSame($responseArray, $transaction->getResponse());
    }

    /**
     * @return array
     */
    public function executeProvider()
    {
        return [
            'successful charge' => [
                'paymentAction' => PaymentMethodInterface::CHARGE,
                'gatewayTransactionType' => Transaction::CHARGE,
                'requestSuccessful' => true,
                'expectedMessage' => '(1) success',
                'transId' => '111',
                'responseArray' => ['1', 'success', '111'],
            ],
            'successful authorize' => [
                'paymentAction' => PaymentMethodInterface::AUTHORIZE,
                'gatewayTransactionType' => Transaction::AUTHORIZE,
                'requestSuccessful' => true,
                'expectedMessage' => '(1) success',
                'transId' => '111',
                'responseArray' => ['1', 'success', '111'],
            ],
        ];
    }

    public function testCapture()
    {
        $authorizeTransaction = $this->createPaymentTransaction(PaymentMethodInterface::AUTHORIZE);

        $transaction = (new PaymentTransaction)
            ->setSourcePaymentTransaction($authorizeTransaction)
            ->setAction(PaymentMethodInterface::CAPTURE);

        $testMode = false;

        $this->paymentConfig->expects($this->any())
            ->method('isTestMode')
            ->willReturn($testMode);

        $this->paymentConfig->expects($this->any())
            ->method('getPurchaseAction')
            ->willReturn(PaymentMethodInterface::CAPTURE);

        $this->paymentConfig->expects($this->any())
            ->method('getApiLoginId')
            ->willReturn('API_LOGIN_ID');

        $this->paymentConfig->expects($this->any())
            ->method('getTransactionKey')
            ->willReturn('API_TRANSACTION_KEY');

        $this->gateway->expects($this->once())
            ->method('setTestMode')
            ->with($testMode);

        $response = $this->prepareSDKResponse(true);

        $this->gateway->expects($this->once())
            ->method('request')
            ->with(Transaction::CAPTURE)
            ->willReturn($response);

        $result = $this->method->execute($transaction->getAction(), $transaction);
        $this->assertArrayHasKey('message', $result);
        $this->assertSame('(1) success', $result['message']);

        $this->assertArrayHasKey('successful', $result);
        $this->assertTrue($result['successful']);

        $this->assertTrue($transaction->isSuccessful());
        $this->assertFalse($transaction->isActive());
        $this->assertNotNull($transaction->getSourcePaymentTransaction());
        $this->assertFalse($transaction->getSourcePaymentTransaction()->isActive());
    }

    public function testValidate()
    {
        $validateTransaction = $this->createPaymentTransaction(PaymentMethodInterface::VALIDATE);

        $testMode = false;

        $this->paymentConfig->expects($this->any())
            ->method('isTestMode')
            ->willReturn($testMode);

        $this->gateway->expects($this->once())
            ->method('setTestMode')
            ->with($testMode);

        $result = $this->method->execute($validateTransaction->getAction(), $validateTransaction);

        $this->assertArrayHasKey('successful', $result);
        $this->assertTrue($result['successful']);

        $this->assertTrue($validateTransaction->isSuccessful());
        $this->assertTrue($validateTransaction->isActive());
        $this->assertEquals(PaymentMethodInterface::VALIDATE, $validateTransaction->getAction());
        $this->assertEquals(0, $validateTransaction->getAmount());
        $this->assertEquals('', $validateTransaction->getCurrency());
    }

    public function testCaptureWithoutSourcePaymentAction()
    {
        $transaction = (new PaymentTransaction)
            ->setAction(PaymentMethodInterface::CAPTURE);

        $testMode = false;

        $this->paymentConfig->expects($this->any())
            ->method('isTestMode')
            ->willReturn($testMode);

        $this->gateway->expects($this->once())
            ->method('setTestMode')
            ->with($testMode);

        $this->gateway->expects($this->never())
            ->method('request');

        $result = $this->method->execute($transaction->getAction(), $transaction);
        $this->assertArrayHasKey('successful', $result);
        $this->assertFalse($result['successful']);

        $this->assertFalse($transaction->isSuccessful());
        $this->assertFalse($transaction->isActive());
    }

    /**
     * @dataProvider incorrectAdditionalDataProvider
     * @param string $expectedExceptionMessage
     * @param array|null $transactionOptions
     */
    public function testIncorrectAdditionalData($expectedExceptionMessage, array $transactionOptions = null)
    {
        $this->expectException(\LogicException::class);
        $this->paymentConfig->expects($this->any())
            ->method('getApiLoginId')
            ->willReturn('API_LOGIN_ID');

        $this->paymentConfig->expects($this->any())
            ->method('getTransactionKey')
            ->willReturn('API_TRANSACTION_KEY');

        $taxProvider = $this->createMock(TaxProviderInterface::class);
        $taxProvider->expects($this->any())
            ->method('getTax')
            ->willReturn(Result::jsonDeserialize(null));

        $this->taxProviderRegistry
            ->expects($this->any())
            ->method('getEnabledProvider')
            ->willReturn($taxProvider);

        $this->expectExceptionMessage($expectedExceptionMessage);

        $transaction = $this->createPaymentTransaction(PaymentMethodInterface::PURCHASE);
        $transaction->getSourcePaymentTransaction()->setTransactionOptions($transactionOptions);
        $this->method->execute($transaction->getAction(), $transaction);
    }

    public function testCorrectAdditionalData()
    {
        $transactionOptions = [
            'additionalData' => json_encode([
                'dataDescriptor' => 'dataDescriptorValue',
                'dataValue' => 'dataValueValue',
            ])
        ];

        $response = $this->prepareSDKResponse(true);
        $this->gateway->expects($this->once())
            ->method('request')
            ->willReturn($response);

        $this->paymentConfig->expects($this->any())
            ->method('getPurchaseAction')
            ->willReturn(PaymentMethodInterface::AUTHORIZE);

        $this->paymentConfig->expects($this->any())
            ->method('getApiLoginId')
            ->willReturn('API_LOGIN_ID');

        $this->paymentConfig->expects($this->any())
            ->method('getTransactionKey')
            ->willReturn('API_TRANSACTION_KEY');

        $taxProvider = $this->createMock(TaxProviderInterface::class);
        $taxProvider->expects($this->once())
            ->method('getTax')
            ->willReturn(Result::jsonDeserialize(null));

        $this->taxProviderRegistry
            ->expects($this->once())
            ->method('getEnabledProvider')
            ->willReturn($taxProvider);

        $transaction = $this->createPaymentTransaction(PaymentMethodInterface::PURCHASE);
        $transaction->getSourcePaymentTransaction()->setTransactionOptions($transactionOptions);
        $result = $this->method->execute($transaction->getAction(), $transaction);
        $this->assertInternalType('array', $result);
    }

    public function testCorrectAdditionalDataWithSaveProfile()
    {
        $transactionOptions = [
            'additionalData' => \json_encode([
                'dataDescriptor' => 'dataDescriptorValue',
                'dataValue' => 'dataValueValue',
                'saveProfile' => true
            ])
        ];

        $response = $this->prepareSDKResponse(true);
        $this->gateway->expects($this->once())
            ->method('request')
            ->willReturn($response);

        $this->paymentConfig->expects($this->any())
            ->method('getPurchaseAction')
            ->willReturn(PaymentMethodInterface::AUTHORIZE);

        $this->paymentConfig->expects($this->any())
            ->method('getApiLoginId')
            ->willReturn('API_LOGIN_ID');

        $this->paymentConfig->expects($this->any())
            ->method('getTransactionKey')
            ->willReturn('API_TRANSACTION_KEY');

        $this->paymentConfig->expects($this->any())
            ->method('isEnabledCIM')
            ->willReturn(true);

        $taxProvider = $this->createMock(TaxProviderInterface::class);
        $taxProvider->expects($this->once())
            ->method('getTax')
            ->willReturn(Result::jsonDeserialize(null));

        $this->taxProviderRegistry
            ->expects($this->once())
            ->method('getEnabledProvider')
            ->willReturn($taxProvider);

        $transaction = $this->createPaymentTransaction(PaymentMethodInterface::PURCHASE);
        $transaction->getSourcePaymentTransaction()->setTransactionOptions($transactionOptions);
        $result = $this->method->execute($transaction->getAction(), $transaction);

        $request = $transaction->getRequest();
        $this->assertArrayHasKey(CreateProfile::NAME, $request);
        $this->assertTrue($request[CreateProfile::NAME]);

        $this->assertInternalType('array', $result);
    }

    public function testCorrectAdditionalDataWithSaveProfileCIMDisabled()
    {
        $transactionOptions = [
            'additionalData' => \json_encode([
                'dataDescriptor' => 'dataDescriptorValue',
                'dataValue' => 'dataValueValue',
                'saveProfile' => true
            ])
        ];

        $response = $this->prepareSDKResponse(true);
        $this->gateway->expects($this->once())
            ->method('request')
            ->willReturn($response);

        $this->paymentConfig->expects($this->any())
            ->method('getPurchaseAction')
            ->willReturn(PaymentMethodInterface::AUTHORIZE);

        $this->paymentConfig->expects($this->any())
            ->method('getApiLoginId')
            ->willReturn('API_LOGIN_ID');

        $this->paymentConfig->expects($this->any())
            ->method('getTransactionKey')
            ->willReturn('API_TRANSACTION_KEY');

        $this->paymentConfig->expects($this->any())
            ->method('isEnabledCIM')
            ->willReturn(false);

        $taxProvider = $this->createMock(TaxProviderInterface::class);
        $taxProvider->expects($this->once())
            ->method('getTax')
            ->willReturn(Result::jsonDeserialize(null));

        $this->taxProviderRegistry
            ->expects($this->once())
            ->method('getEnabledProvider')
            ->willReturn($taxProvider);

        $transaction = $this->createPaymentTransaction(PaymentMethodInterface::PURCHASE);
        $transaction->getSourcePaymentTransaction()->setTransactionOptions($transactionOptions);
        $result = $this->method->execute($transaction->getAction(), $transaction);

        $this->assertArrayNotHasKey(CreateProfile::NAME, $transaction->getRequest());
        $this->assertInternalType('array', $result);
    }

    /**
     * @param bool $expected
     * @param string $actionName
     *
     * @dataProvider supportsDataProvider
     */
    public function testSupports($expected, $actionName)
    {
        $this->assertEquals($expected, $this->method->supports($actionName));
    }

    public function testIsApplicableWithValidRequest()
    {
        $isConnectionSecure = true;
        $request = $this->createMock(Request::class);
        $request->expects($this->once())
            ->method('isSecure')
            ->willReturn($isConnectionSecure);

        $this->requestStack->expects($this->once())
            ->method('getCurrentRequest')
            ->willReturn($request);

        /** @var PaymentContextInterface|\PHPUnit\Framework\MockObject\MockObject $context */
        $context = $this->createMock(PaymentContextInterface::class);
        $this->assertTrue($this->method->isApplicable($context));
    }

    public function testIsApplicableWithoutCurrentRequest()
    {
        $this->requestStack->expects($this->once())
            ->method('getCurrentRequest')
            ->willReturn(null);

        /** @var PaymentContextInterface|\PHPUnit\Framework\MockObject\MockObject $context */
        $context = $this->createMock(PaymentContextInterface::class);
        $this->assertTrue($this->method->isApplicable($context));
    }

    public function testGetIdentifier()
    {
        $this->paymentConfig->expects($this->once())
            ->method('getPaymentMethodIdentifier')
            ->willReturn('authorize_net');

        $this->assertSame('authorize_net', $this->method->getIdentifier());
    }

    /**
     * @param bool $requestSuccessful
     * @return AuthorizeNetSDKTransactionResponse
     */
    protected function prepareSDKResponse($requestSuccessful)
    {
        $transactionResponse = new TransactionResponseType();
        if ($requestSuccessful === true) {
            $resultCode  = 'Ok';
            $responseCode = '1';
            $message = 'success';
            $transactionId = '111';

            $transactionResponse->setMessages([]);
        } else {
            $resultCode  = 'Error';
            $responseCode = '0';
            $message = 'fail';
            $transactionId = null;

            $transactionResponse->setErrors([]);
        }

        $apiMessage = (new MessagesType\MessageAType)->setCode($responseCode)->setText($message);
        $apiMessageType = (new MessagesType)->setResultCode($resultCode)->setMessage([$apiMessage]);

        $transactionResponse->setResponseCode($responseCode);
        $transactionResponse->setTransId($transactionId);

        /** @var CreateTransactionResponse|\PHPUnit\Framework\MockObject\MockObject $apiResponse */
        $apiResponse = $this->createMock(CreateTransactionResponse::class);
        $apiResponse->expects($this->any())
            ->method('getMessages')
            ->willReturn($apiMessageType);

        $apiResponse->expects($this->any())
            ->method('getTransactionResponse')
            ->willReturn($transactionResponse);

        $this->serializer->expects($this->once())
            ->method('toArray')
            ->with($apiResponse)
            ->willReturn([$responseCode, $message, $transactionId]);

        return new AuthorizeNetSDKTransactionResponse($this->serializer, $apiResponse);
    }

    /**
     * @param string $paymentAction
     * @param int $profileId
     * @param bool $saveProfile
     * @return PaymentTransaction
     */
    protected function createPaymentTransaction($paymentAction, int $profileId = null, bool $saveProfile = null)
    {
        $sourcePaymentTransaction = new PaymentTransaction();

        $additionalData = [];
        if (null !== $profileId) {
            $additionalData['profileId'] = $profileId;
            if (null !== $saveProfile) {
                $additionalData['saveProfile'] = $saveProfile;
            }
        } else {
            $additionalData['dataDescriptor'] = 'data_descriptor_value';
            $additionalData['dataValue'] = 'data_value_value';
        }

        $sourcePaymentTransaction->setTransactionOptions(['additionalData' => \json_encode($additionalData)]);

        $paymentTransaction = new PaymentTransaction();
        $paymentTransaction
            ->setCurrency('USD')
            ->setAction($paymentAction)
            ->setSourcePaymentTransaction($sourcePaymentTransaction->setFrontendOwner($this->frontendOwner))
            ->setFrontendOwner($this->frontendOwner)
        ;

        return $paymentTransaction;
    }

    /**
     * @return array
     */
    public function purchaseExecuteProvider()
    {
        return [
            'successful charge' => [
                'purchaseAction' => PaymentMethodInterface::CHARGE,
                'gatewayTransactionType' => Transaction::CHARGE,
                'requestSuccessful' => true,
                'expectedMessage' => '(1) success',
                'transId' => '111',
                'responseArray' => ['1', 'success', '111'],
            ],
            'successful authorize' => [
                'purchaseAction' => PaymentMethodInterface::AUTHORIZE,
                'gatewayTransactionType' => Transaction::AUTHORIZE,
                'requestSuccessful' => true,
                'expectedMessage' => '(1) success',
                'transId' => '111',
                'responseArray' => ['1', 'success', '111'],
            ],
            'unsuccessful authorize' => [
                'purchaseAction' => PaymentMethodInterface::AUTHORIZE,
                'gatewayTransactionType' => Transaction::AUTHORIZE,
                'requestSuccessful' => false,
                'expectedMessage' => '(0) fail',
                'transId' => null,
                'responseArray' => ['0', 'fail', null],
            ],
            'successful authorize charge customer profile' => [
                'purchaseAction' => PaymentMethodInterface::AUTHORIZE,
                'gatewayTransactionType' => Transaction::AUTHORIZE,
                'requestSuccessful' => true,
                'expectedMessage' => '(1) success',
                'transId' => '111',
                'responseArray' => ['1', 'success', '111'],
                'profileId' => 1001
            ],
            'successful authorize charge credit card create profile' => [
                'purchaseAction' => PaymentMethodInterface::AUTHORIZE,
                'gatewayTransactionType' => Transaction::AUTHORIZE,
                'requestSuccessful' => true,
                'expectedMessage' => '(1) success',
                'transId' => '111',
                'responseArray' => ['1', 'success', '111'],
                'saveProfile' => true
            ],
        ];
    }

    /**
     * @return array
     */
    public function supportsDataProvider()
    {
        return [
            [true, PaymentMethodInterface::AUTHORIZE],
            [true, PaymentMethodInterface::CAPTURE],
            [true, PaymentMethodInterface::CHARGE],
            [true, PaymentMethodInterface::PURCHASE],
            [true, PaymentMethodInterface::VALIDATE],
        ];
    }

    /**
     * @return array
     */
    public function incorrectAdditionalDataProvider()
    {
        return [
            'nullable transaction options' => [
                'expectedExceptionMessage' => 'Cant extract additionalData from transaction',
                'transactionOptions' => null,
            ],
            'empty array of transaction options' => [
                'expectedExceptionMessage' => 'Cant extract additionalData from transaction',
                'transactionOptions' => [],
            ],
            'nullable additional data' => [
                'expectedExceptionMessage' => 'Additional data must be an array',
                'transactionOptions' => ['additionalData' => null],
            ],
            'non-json additional data' => [
                'expectedExceptionMessage' => 'Additional data must be an array',
                'transactionOptions' => ['additionalData' => 'apiLoginId,transactionKey'],
            ],
            'json additional data only with dataDescriptor' => [
                'expectedExceptionMessage' => 'Can not find field "dataValue" in additional data',
                'transactionOptions' => ['additionalData' => json_encode(['dataDescriptor' => 'value'])]
            ],
            'json additional data only with dataValue' => [
                'expectedExceptionMessage' => 'Can not find field "dataDescriptor" in additional data',
                'transactionOptions' => ['additionalData' => json_encode(['dataValue' => 'value'])]
            ],
        ];
    }
}
