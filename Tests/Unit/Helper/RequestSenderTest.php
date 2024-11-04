<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\Helper;

use Doctrine\ORM\EntityRepository;
use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Region;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Client\ClientInterface;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Gateway;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Request;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Request\RequestRegistry;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Response\AuthorizeNetSDKResponse;
use Oro\Bundle\AuthorizeNetBundle\Entity\CustomerPaymentProfile;
use Oro\Bundle\AuthorizeNetBundle\Entity\CustomerProfile;
use Oro\Bundle\AuthorizeNetBundle\Exception\CustomerPaymentProfileNotFoundException;
use Oro\Bundle\AuthorizeNetBundle\Helper\MerchantCustomerIdGenerator;
use Oro\Bundle\AuthorizeNetBundle\Helper\RequestSender;
use Oro\Bundle\AuthorizeNetBundle\Method\Config\AuthorizeNetConfig;
use Oro\Bundle\AuthorizeNetBundle\Model\DTO\PaymentProfileAddressDTO;
use Oro\Bundle\AuthorizeNetBundle\Model\DTO\PaymentProfileDTO;
use Oro\Bundle\AuthorizeNetBundle\Model\DTO\PaymentProfileEncodedDataDTO;
use Oro\Bundle\AuthorizeNetBundle\Model\DTO\PaymentProfileMaskedDataDTO;
use Oro\Bundle\AuthorizeNetBundle\Provider\CIMEnabledIntegrationConfigProvider;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Component\Testing\Unit\EntityTrait;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class RequestSenderTest extends \PHPUnit\Framework\TestCase
{
    use EntityTrait;

    private const CUSTOMER_PROFILE_ID = '999';
    private const PAYMENT_PROFILE_ID = '888';
    private const EMAIL = 'example@oroinc.com';
    private const API_LOGIN_ID = 'api_login';
    private const TRANSACTION_KEY = 'transaction_key';

    private static $paymentProfileData = [
        'bill_to' => [
            'phone_number' => 'phone number',
            'fax_number' => 'fax number',
            'address' => 'street address',
            'first_name' => 'first name',
            'last_name' => 'last name',
            'company' => 'company',
            'country' => 'XXX',
            'state' => 'state',
            'city' => 'city',
            'zip' => 'zip'
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

    /** @var Gateway|\PHPUnit\Framework\MockObject\MockObject */
    private $gateway;

    /** @var RequestSender */
    private $requestSender;

    /** @var ClientInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $client;

    /** @var DoctrineHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $doctrineHelper;

    /** @var CIMEnabledIntegrationConfigProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $configProvider;

    private static $config = [
        AuthorizeNetConfig::API_LOGIN_ID => self::API_LOGIN_ID,
        AuthorizeNetConfig::TRANSACTION_KEY => self::TRANSACTION_KEY,
        AuthorizeNetConfig::TEST_MODE_KEY => true
    ];

    #[\Override]
    protected function setUp(): void
    {
        $requestRegistry = new RequestRegistry([
            new Request\CreateCustomerPaymentProfileRequest(),
            new Request\UpdateCustomerPaymentProfileRequest(),
            new Request\DeleteCustomerPaymentProfileRequest(),
            new Request\CreateCustomerProfileRequest(),
            new Request\DeleteCustomerProfileRequest(),
            new Request\GetCustomerPaymentProfileRequest(),
            new Request\GetCustomerProfileRequest()
        ]);

        $this->client = $this->createMock(ClientInterface::class);
        $this->gateway = new Gateway($this->client, $requestRegistry);

        $this->doctrineHelper = $this->createMock(DoctrineHelper::class);
        $this->configProvider = $this->createMock(CIMEnabledIntegrationConfigProvider::class);

        $this->configProvider->expects($this->once())
            ->method('getConfig')
            ->willReturn(new AuthorizeNetConfig(self::$config));

        $merchantCustomerIdGenerator = new MerchantCustomerIdGenerator();
        $this->requestSender = new RequestSender(
            $this->gateway,
            $this->doctrineHelper,
            $this->configProvider,
            $merchantCustomerIdGenerator
        );
    }

    /**
     * @dataProvider successfulResponseProvider
     */
    public function testCreateCustomerProfile(AuthorizeNetSDKResponse $response)
    {
        $paymentProfileDTO = $this->buildPaymentProfileDTO();
        $customerProfile = $paymentProfileDTO->getProfile()->getCustomerProfile();

        $options = array_merge(
            $this->buildAuthOptions(),
            [
                Option\Email::EMAIL => self::EMAIL,
                Option\MerchantCustomerId::MERCHANT_CUSTOMER_ID => 'oro-33-22'
            ]
        );

        $this->client->expects($this->once())
            ->method('send')
            ->with(Gateway::ADDRESS_SANDBOX, Request\CreateCustomerProfileRequest::REQUEST_TYPE, $options)
            ->willReturn($response);

        $result = $this->requestSender->createCustomerProfile($customerProfile);
        $this->assertEquals(self::CUSTOMER_PROFILE_ID, $result);
    }

    /**
     * @dataProvider unsuccessfulResponseProvider
     */
    public function testCreateCustomerProfileException(
        AuthorizeNetSDKResponse $response,
        string $expectedException,
        string $exceptionMessage
    ) {
        $paymentProfileDTO = $this->buildPaymentProfileDTO();
        $customerProfile = $paymentProfileDTO->getProfile()->getCustomerProfile();

        $options = array_merge(
            $this->buildAuthOptions(),
            [
                Option\Email::EMAIL => self::EMAIL,
                Option\MerchantCustomerId::MERCHANT_CUSTOMER_ID => 'oro-33-22'
            ]
        );

        $this->client->expects($this->once())
            ->method('send')
            ->with(Gateway::ADDRESS_SANDBOX, Request\CreateCustomerProfileRequest::REQUEST_TYPE, $options)
            ->willReturn($response);

        $this->expectException($expectedException);
        $this->expectExceptionMessage($exceptionMessage);

        $this->requestSender->createCustomerProfile($customerProfile);
    }

    /**
     * @dataProvider successfulResponseProvider
     */
    public function testDeleteCustomerProfile(AuthorizeNetSDKResponse $response)
    {
        $paymentProfileDTO = $this->buildPaymentProfileDTO();
        $customerProfile = $paymentProfileDTO->getProfile()->getCustomerProfile();

        $options = array_merge(
            $this->buildAuthOptions(),
            [
                Option\CustomerProfileId::CUSTOMER_PROFILE_ID => self::CUSTOMER_PROFILE_ID
            ]
        );

        $this->client->expects($this->once())
            ->method('send')
            ->with(Gateway::ADDRESS_SANDBOX, Request\DeleteCustomerProfileRequest::REQUEST_TYPE, $options)
            ->willReturn($response);

        $result = $this->requestSender->deleteCustomerProfile($customerProfile);
        $this->assertEquals(true, $result);
    }

    /**
     * @dataProvider unsuccessfulResponseProvider
     */
    public function testDeleteCustomerProfileException(
        AuthorizeNetSDKResponse $response,
        string $expectedException,
        string $exceptionMessage
    ) {
        $paymentProfileDTO = $this->buildPaymentProfileDTO();
        $customerProfile = $paymentProfileDTO->getProfile()->getCustomerProfile();

        $options = array_merge(
            $this->buildAuthOptions(),
            [
                Option\CustomerProfileId::CUSTOMER_PROFILE_ID => self::CUSTOMER_PROFILE_ID
            ]
        );

        $this->client->expects($this->once())
            ->method('send')
            ->with(Gateway::ADDRESS_SANDBOX, Request\DeleteCustomerProfileRequest::REQUEST_TYPE, $options)
            ->willReturn($response);

        $this->expectException($expectedException);
        $this->expectExceptionMessage($exceptionMessage);

        $this->requestSender->deleteCustomerProfile($customerProfile);
    }

    /**
     * @dataProvider successfulResponseProvider
     */
    public function testDeleteCustomerPaymentProfile(AuthorizeNetSDKResponse $response)
    {
        $paymentProfileDTO = $this->buildPaymentProfileDTO();
        $paymentProfile = $paymentProfileDTO->getProfile();

        $options = array_merge(
            $this->buildAuthOptions(),
            [
                Option\CustomerProfileId::CUSTOMER_PROFILE_ID => self::CUSTOMER_PROFILE_ID,
                Option\CustomerPaymentProfileId::CUSTOMER_PAYMENT_PROFILE_ID => self::PAYMENT_PROFILE_ID
            ]
        );

        $this->client->expects($this->once())
            ->method('send')
            ->with(Gateway::ADDRESS_SANDBOX, Request\DeleteCustomerPaymentProfileRequest::REQUEST_TYPE, $options)
            ->willReturn($response);

        $result = $this->requestSender->deleteCustomerPaymentProfile($paymentProfile);
        $this->assertEquals(true, $result);
    }

    /**
     * @dataProvider unsuccessfulResponseProvider
     */
    public function testDeleteCustomerPaymentProfileException(
        AuthorizeNetSDKResponse $response,
        string $expectedException,
        string $exceptionMessage
    ) {
        $paymentProfileDTO = $this->buildPaymentProfileDTO();
        $paymentProfile = $paymentProfileDTO->getProfile();

        $options = array_merge(
            $this->buildAuthOptions(),
            [
                Option\CustomerProfileId::CUSTOMER_PROFILE_ID => self::CUSTOMER_PROFILE_ID,
                Option\CustomerPaymentProfileId::CUSTOMER_PAYMENT_PROFILE_ID => self::PAYMENT_PROFILE_ID
            ]
        );

        $this->client->expects($this->once())
            ->method('send')
            ->with(Gateway::ADDRESS_SANDBOX, Request\DeleteCustomerPaymentProfileRequest::REQUEST_TYPE, $options)
            ->willReturn($response);

        $this->expectException($expectedException);
        $this->expectExceptionMessage($exceptionMessage);

        $this->requestSender->deleteCustomerPaymentProfile($paymentProfile);
    }

    /**
     * @dataProvider successfulResponseProvider
     */
    public function testGetCustomerPaymentProfile(AuthorizeNetSDKResponse $response)
    {
        $paymentProfileDTO = $this->buildPaymentProfileDTO();
        $paymentProfile = $paymentProfileDTO->getProfile();

        $options = array_merge(
            $this->buildAuthOptions(),
            [
                Option\CustomerProfileId::CUSTOMER_PROFILE_ID => self::CUSTOMER_PROFILE_ID,
                Option\CustomerPaymentProfileId::CUSTOMER_PAYMENT_PROFILE_ID => self::PAYMENT_PROFILE_ID
            ]
        );

        $this->client->expects($this->once())
            ->method('send')
            ->with(Gateway::ADDRESS_SANDBOX, Request\GetCustomerPaymentProfileRequest::REQUEST_TYPE, $options)
            ->willReturn($response);

        $result = $this->requestSender->getCustomerPaymentProfile($paymentProfile);
        $this->assertEquals(self::$paymentProfileData, $result);
    }

    /**
     * @dataProvider unsuccessfulResponseProvider
     */
    public function testGetCustomerPaymentProfileException(
        AuthorizeNetSDKResponse $response,
        string $expectedException,
        string $exceptionMessage
    ) {
        $paymentProfileDTO = $this->buildPaymentProfileDTO();
        $paymentProfile = $paymentProfileDTO->getProfile();

        $options = array_merge(
            $this->buildAuthOptions(),
            [
                Option\CustomerProfileId::CUSTOMER_PROFILE_ID => self::CUSTOMER_PROFILE_ID,
                Option\CustomerPaymentProfileId::CUSTOMER_PAYMENT_PROFILE_ID => self::PAYMENT_PROFILE_ID
            ]
        );

        $this->client->expects($this->once())
            ->method('send')
            ->with(Gateway::ADDRESS_SANDBOX, Request\GetCustomerPaymentProfileRequest::REQUEST_TYPE, $options)
            ->willReturn($response);

        $this->expectException(CustomerPaymentProfileNotFoundException::class);
        $this->expectExceptionMessage($exceptionMessage);

        $this->requestSender->getCustomerPaymentProfile($paymentProfile);
    }

    public function testGetCustomerProfile()
    {
        $paymentProfileDTO = $this->buildPaymentProfileDTO();
        $customerProfile = $paymentProfileDTO->getProfile()->getCustomerProfile();

        $options = array_merge(
            $this->buildAuthOptions(),
            [
                Option\CustomerProfileId::CUSTOMER_PROFILE_ID => self::CUSTOMER_PROFILE_ID
            ]
        );

        $paymentProfilesData = [
            'payment_profiles' => [
                'customer_payment_profile_id' => self::CUSTOMER_PROFILE_ID
            ]
        ];

        $data = [
            'profile' => $paymentProfilesData
        ];

        $response = $this->createMock(AuthorizeNetSDKResponse::class);
        $response->expects($this->any())
            ->method('isSuccessful')
            ->willReturn(true);
        $response->expects($this->any())
            ->method('getData')
            ->willReturn($data);

        $this->client->expects($this->once())
            ->method('send')
            ->with(Gateway::ADDRESS_SANDBOX, Request\GetCustomerProfileRequest::REQUEST_TYPE, $options)
            ->willReturn($response);

        $result = $this->requestSender->getCustomerProfile($customerProfile);
        $this->assertEquals($paymentProfilesData, $result);
    }

    /**
     * @dataProvider unsuccessfulResponseProvider
     */
    public function testGetCustomerProfileException(
        AuthorizeNetSDKResponse $response,
        string $expectedException,
        string $exceptionMessage
    ) {
        $paymentProfileDTO = $this->buildPaymentProfileDTO();
        $customerProfile = $paymentProfileDTO->getProfile()->getCustomerProfile();

        $options = array_merge(
            $this->buildAuthOptions(),
            [
                Option\CustomerProfileId::CUSTOMER_PROFILE_ID => self::CUSTOMER_PROFILE_ID
            ]
        );

        $this->client->expects($this->once())
            ->method('send')
            ->with(Gateway::ADDRESS_SANDBOX, Request\GetCustomerProfileRequest::REQUEST_TYPE, $options)
            ->willReturn($response);

        $result = $this->requestSender->getCustomerProfile($customerProfile);
        $this->assertSame([], $result);
    }

    /**
     * @dataProvider successfulResponseProvider
     */
    public function testCreateCustomerPaymentProfile(AuthorizeNetSDKResponse $response)
    {
        $paymentProfileDTO = $this->buildPaymentProfileDTO();
        $addressData = $paymentProfileDTO->getAddress();
        $paymentProfile = $paymentProfileDTO->getProfile();
        $encodedData = $paymentProfileDTO->getEncodedData();

        $options = array_merge(
            $this->buildAuthOptions(),
            $this->buildAddressOptions($addressData),
            [
                Option\CustomerProfileId::CUSTOMER_PROFILE_ID => self::CUSTOMER_PROFILE_ID,
                Option\DataDescriptor::DATA_DESCRIPTOR => $encodedData->getDescriptor(),
                Option\DataValue::DATA_VALUE => $encodedData->getValue(),
                Option\IsDefault::IS_DEFAULT => $paymentProfile->isDefault(),
                Option\ValidationMode::VALIDATION_MODE => Option\ValidationMode::TEST_MODE
            ]
        );

        $this->client->expects($this->once())
            ->method('send')
            ->with(Gateway::ADDRESS_SANDBOX, Request\CreateCustomerPaymentProfileRequest::REQUEST_TYPE, $options)
            ->willReturn($response);

        $result = $this->requestSender->createCustomerPaymentProfile($paymentProfileDTO);
        $this->assertEquals(self::PAYMENT_PROFILE_ID, $result);
    }

    /**
     * @dataProvider unsuccessfulResponseProvider
     */
    public function testCreateCustomerPaymentProfileException(
        AuthorizeNetSDKResponse $response,
        string $expectedException,
        string $exceptionMessage
    ) {
        $paymentProfileDTO = $this->buildPaymentProfileDTO();
        $addressData = $paymentProfileDTO->getAddress();
        $paymentProfile = $paymentProfileDTO->getProfile();
        $encodedData = $paymentProfileDTO->getEncodedData();

        $options = array_merge(
            $this->buildAuthOptions(),
            $this->buildAddressOptions($addressData),
            [
                Option\CustomerProfileId::CUSTOMER_PROFILE_ID => self::CUSTOMER_PROFILE_ID,
                Option\DataDescriptor::DATA_DESCRIPTOR => $encodedData->getDescriptor(),
                Option\DataValue::DATA_VALUE => $encodedData->getValue(),
                Option\IsDefault::IS_DEFAULT => $paymentProfile->isDefault(),
                Option\ValidationMode::VALIDATION_MODE => Option\ValidationMode::TEST_MODE
            ]
        );

        $this->client->expects($this->once())
            ->method('send')
            ->with(Gateway::ADDRESS_SANDBOX, Request\CreateCustomerPaymentProfileRequest::REQUEST_TYPE, $options)
            ->willReturn($response);

        $this->expectException($expectedException);
        $this->expectExceptionMessage($exceptionMessage);

        $this->requestSender->createCustomerPaymentProfile($paymentProfileDTO);
    }

    /**
     * @dataProvider successfulResponseProvider
     */
    public function testUpdateCustomerPaymentProfileNoCreditCardUpdate(AuthorizeNetSDKResponse $response)
    {
        $paymentProfileDTO = $this->buildPaymentProfileDTO();
        $paymentProfileDTO->setUpdatePaymentData(false);
        $addressData = $paymentProfileDTO->getAddress();
        $paymentProfile = $paymentProfileDTO->getProfile();

        $options = array_merge(
            $this->buildAuthOptions(),
            $this->buildAddressOptions($addressData),
            [
                Option\CustomerProfileId::CUSTOMER_PROFILE_ID => self::CUSTOMER_PROFILE_ID,
                Option\CustomerPaymentProfileId::CUSTOMER_PAYMENT_PROFILE_ID => self::PAYMENT_PROFILE_ID,
                Option\IsDefault::IS_DEFAULT => $paymentProfile->isDefault(),
                Option\ValidationMode::VALIDATION_MODE => Option\ValidationMode::TEST_MODE,
                Option\UpdatePaymentData::UPDATE_PAYMENT_DATA => $paymentProfileDTO->isUpdatePaymentData(),
                Option\ProfileType::PROFILE_TYPE => $paymentProfile->getType(),
                Option\CardNumber::CARD_NUMBER => 'XXXX1234',
                Option\ExpirationDate::EXPIRATION_DATE => 'XXXX'
            ]
        );

        $this->client->expects($this->once())
            ->method('send')
            ->with(Gateway::ADDRESS_SANDBOX, Request\UpdateCustomerPaymentProfileRequest::REQUEST_TYPE, $options)
            ->willReturn($response);

        $result = $this->requestSender->updateCustomerPaymentProfile($paymentProfileDTO);
        $this->assertEquals(true, $result);
    }

    /**
     * @dataProvider unsuccessfulResponseProvider
     */
    public function testUpdateCustomerPaymentProfileNoCreditCardUpdateException(
        AuthorizeNetSDKResponse $response,
        string $expectedException,
        string $exceptionMessage
    ) {
        $paymentProfileDTO = $this->buildPaymentProfileDTO();
        $paymentProfileDTO->setUpdatePaymentData(false);
        $addressData = $paymentProfileDTO->getAddress();
        $paymentProfile = $paymentProfileDTO->getProfile();

        $options = array_merge(
            $this->buildAuthOptions(),
            $this->buildAddressOptions($addressData),
            [
                Option\CustomerProfileId::CUSTOMER_PROFILE_ID => self::CUSTOMER_PROFILE_ID,
                Option\CustomerPaymentProfileId::CUSTOMER_PAYMENT_PROFILE_ID => self::PAYMENT_PROFILE_ID,
                Option\IsDefault::IS_DEFAULT => $paymentProfile->isDefault(),
                Option\ValidationMode::VALIDATION_MODE => Option\ValidationMode::TEST_MODE,
                Option\UpdatePaymentData::UPDATE_PAYMENT_DATA => $paymentProfileDTO->isUpdatePaymentData(),
                Option\ProfileType::PROFILE_TYPE => $paymentProfile->getType(),
                Option\CardNumber::CARD_NUMBER => 'XXXX1234',
                Option\ExpirationDate::EXPIRATION_DATE => 'XXXX'
            ]
        );

        $this->client->expects($this->once())
            ->method('send')
            ->with(Gateway::ADDRESS_SANDBOX, Request\UpdateCustomerPaymentProfileRequest::REQUEST_TYPE, $options)
            ->willReturn($response);

        $this->expectException($expectedException);
        $this->expectExceptionMessage($exceptionMessage);

        $this->requestSender->updateCustomerPaymentProfile($paymentProfileDTO);
    }

    /**
     * @dataProvider successfulResponseProvider
     */
    public function testUpdateCustomerPaymentProfileWithCreditCardUpdate(AuthorizeNetSDKResponse $response)
    {
        $paymentProfileDTO = $this->buildPaymentProfileDTO();
        $paymentProfileDTO->setUpdatePaymentData(true);
        $addressData = $paymentProfileDTO->getAddress();
        $paymentProfile = $paymentProfileDTO->getProfile();
        $encodedData = $paymentProfileDTO->getEncodedData();

        $options = array_merge(
            $this->buildAuthOptions(),
            $this->buildAddressOptions($addressData),
            [
                Option\CustomerProfileId::CUSTOMER_PROFILE_ID => self::CUSTOMER_PROFILE_ID,
                Option\CustomerPaymentProfileId::CUSTOMER_PAYMENT_PROFILE_ID => self::PAYMENT_PROFILE_ID,
                Option\IsDefault::IS_DEFAULT => $paymentProfile->isDefault(),
                Option\ValidationMode::VALIDATION_MODE => Option\ValidationMode::TEST_MODE,
                Option\UpdatePaymentData::UPDATE_PAYMENT_DATA => $paymentProfileDTO->isUpdatePaymentData(),
                Option\ProfileType::PROFILE_TYPE => $paymentProfile->getType(),
                Option\DataDescriptor::DATA_DESCRIPTOR => $encodedData->getDescriptor(),
                Option\DataValue::DATA_VALUE => $encodedData->getValue()
            ]
        );

        $this->client->expects($this->once())
            ->method('send')
            ->with(Gateway::ADDRESS_SANDBOX, Request\UpdateCustomerPaymentProfileRequest::REQUEST_TYPE, $options)
            ->willReturn($response);

        $result = $this->requestSender->updateCustomerPaymentProfile($paymentProfileDTO);
        $this->assertEquals(true, $result);
    }

    /**
     * @dataProvider unsuccessfulResponseProvider
     */
    public function testUpdateCustomerPaymentProfileWithCreditCardUpdateException(
        AuthorizeNetSDKResponse $response,
        string $expectedException,
        string $exceptionMessage
    ) {
        $paymentProfileDTO = $this->buildPaymentProfileDTO();
        $paymentProfileDTO->setUpdatePaymentData(true);
        $addressData = $paymentProfileDTO->getAddress();
        $paymentProfile = $paymentProfileDTO->getProfile();
        $encodedData = $paymentProfileDTO->getEncodedData();

        $options = array_merge(
            $this->buildAuthOptions(),
            $this->buildAddressOptions($addressData),
            [
                Option\CustomerProfileId::CUSTOMER_PROFILE_ID => self::CUSTOMER_PROFILE_ID,
                Option\CustomerPaymentProfileId::CUSTOMER_PAYMENT_PROFILE_ID => self::PAYMENT_PROFILE_ID,
                Option\IsDefault::IS_DEFAULT => $paymentProfile->isDefault(),
                Option\ValidationMode::VALIDATION_MODE => Option\ValidationMode::TEST_MODE,
                Option\UpdatePaymentData::UPDATE_PAYMENT_DATA => $paymentProfileDTO->isUpdatePaymentData(),
                Option\ProfileType::PROFILE_TYPE => $paymentProfile->getType(),
                Option\DataDescriptor::DATA_DESCRIPTOR => $encodedData->getDescriptor(),
                Option\DataValue::DATA_VALUE => $encodedData->getValue()
            ]
        );

        $this->client->expects($this->once())
            ->method('send')
            ->with(Gateway::ADDRESS_SANDBOX, Request\UpdateCustomerPaymentProfileRequest::REQUEST_TYPE, $options)
            ->willReturn($response);

        $this->expectException($expectedException);
        $this->expectExceptionMessage($exceptionMessage);

        $this->requestSender->updateCustomerPaymentProfile($paymentProfileDTO);
    }

    /**
     * @dataProvider successfulResponseProvider
     */
    public function testGetPaymentProfileAddressDTO(AuthorizeNetSDKResponse $response)
    {
        $paymentProfileDTO = $this->buildPaymentProfileDTO();
        $paymentProfile = $paymentProfileDTO->getProfile();
        $addressData = $paymentProfileDTO->getAddress();

        $countryRepository = $this->createMock(EntityRepository::class);
        $countryRepository->expects($this->any())
            ->method('findOneBy')
            ->willReturn($addressData->getCountry());

        $regionRepository = $this->createMock(EntityRepository::class);
        $regionRepository->expects($this->any())
            ->method('findOneBy')
            ->willReturn($addressData->getRegion());

        $this->doctrineHelper->expects($this->any())
            ->method('getEntityRepository')
            ->willReturnMap([
                [Country::class, $countryRepository],
                [Region::class, $regionRepository]
            ]);

        $options = [
            Option\ApiLoginId::API_LOGIN_ID => self::API_LOGIN_ID,
            Option\TransactionKey::TRANSACTION_KEY => self::TRANSACTION_KEY,
            Option\CustomerProfileId::CUSTOMER_PROFILE_ID => self::CUSTOMER_PROFILE_ID,
            Option\CustomerPaymentProfileId::CUSTOMER_PAYMENT_PROFILE_ID => self::PAYMENT_PROFILE_ID,
        ];

        $this->client->expects($this->once())
            ->method('send')
            ->with(Gateway::ADDRESS_SANDBOX, Request\GetCustomerPaymentProfileRequest::REQUEST_TYPE, $options)
            ->willReturn($response);

        $paymentProfileData = $this->requestSender->getCustomerPaymentProfile($paymentProfile);

        $result = $this->requestSender->getPaymentProfileAddressDTO($paymentProfileData['bill_to']);
        $this->assertEquals($addressData, $result);
    }

    /**
     * @dataProvider successfulResponseProvider
     */
    public function testGetPaymentProfileMaskedDataDTO(AuthorizeNetSDKResponse $response)
    {
        $paymentProfileDTO = $this->buildPaymentProfileDTO();
        $paymentProfile = $paymentProfileDTO->getProfile();
        $maskedData = $paymentProfileDTO->getMaskedData();

        $options = [
            Option\ApiLoginId::API_LOGIN_ID => self::API_LOGIN_ID,
            Option\TransactionKey::TRANSACTION_KEY => self::TRANSACTION_KEY,
            Option\CustomerProfileId::CUSTOMER_PROFILE_ID => self::CUSTOMER_PROFILE_ID,
            Option\CustomerPaymentProfileId::CUSTOMER_PAYMENT_PROFILE_ID => self::PAYMENT_PROFILE_ID,
        ];

        $this->client->expects($this->once())
            ->method('send')
            ->with(Gateway::ADDRESS_SANDBOX, Request\GetCustomerPaymentProfileRequest::REQUEST_TYPE, $options)
            ->willReturn($response);

        $paymentProfileData = $this->requestSender->getCustomerPaymentProfile($paymentProfile);

        $result = $this->requestSender->getPaymentProfileMaskedDataDTO($paymentProfileData['payment']);
        $this->assertEquals($maskedData, $result);
    }

    /**
     * @dataProvider successfulResponseProvider
     */
    public function testUpdateCustomerPaymentProfileNoBankAccountUpdate(AuthorizeNetSDKResponse $response)
    {
        $paymentProfileDTO = $this->buildPaymentProfileDTO(CustomerPaymentProfile::TYPE_ECHECK);
        $paymentProfileDTO->setUpdatePaymentData(false);
        $addressData = $paymentProfileDTO->getAddress();
        $paymentProfile = $paymentProfileDTO->getProfile();

        $options = array_merge(
            $this->buildAuthOptions(),
            $this->buildAddressOptions($addressData),
            [
                Option\CustomerProfileId::CUSTOMER_PROFILE_ID => self::CUSTOMER_PROFILE_ID,
                Option\CustomerPaymentProfileId::CUSTOMER_PAYMENT_PROFILE_ID => self::PAYMENT_PROFILE_ID,
                Option\IsDefault::IS_DEFAULT => $paymentProfile->isDefault(),
                Option\ValidationMode::VALIDATION_MODE => Option\ValidationMode::TEST_MODE,
                Option\UpdatePaymentData::UPDATE_PAYMENT_DATA => $paymentProfileDTO->isUpdatePaymentData(),
                Option\ProfileType::PROFILE_TYPE => $paymentProfile->getType(),
                Option\AccountNumber::ACCOUNT_NUMBER => 'XXXX1234',
                Option\RoutingNumber::ROUTING_NUMBER => 'XXXX4321',
                Option\NameOnAccount::NAME_ON_ACCOUNT => 'first last',
                Option\AccountType::ACCOUNT_TYPE => 'account type',
                Option\BankName::BANK_NAME => 'bank name'
            ]
        );

        $this->client->expects($this->once())
            ->method('send')
            ->with(Gateway::ADDRESS_SANDBOX, Request\UpdateCustomerPaymentProfileRequest::REQUEST_TYPE, $options)
            ->willReturn($response);

        $result = $this->requestSender->updateCustomerPaymentProfile($paymentProfileDTO);
        $this->assertEquals(true, $result);
    }

    public function successfulResponseProvider(): array
    {
        $universalData = [
            'customer_profile_id' => self::CUSTOMER_PROFILE_ID,
            'customer_payment_profile_id' => self::PAYMENT_PROFILE_ID,
            'payment_profile' => self::$paymentProfileData
        ];

        $successfulResponse = $this->createMock(AuthorizeNetSDKResponse::class);
        $successfulResponse->expects($this->any())
            ->method('isSuccessful')
            ->willReturn(true);
        $successfulResponse->expects($this->any())
            ->method('getData')
            ->willReturn($universalData);

        return [
            'successful response' => [
                'responseMock' => $successfulResponse,
            ]
        ];
    }

    public function unsuccessfulResponseProvider(): array
    {
        $unsuccessfulResponse = $this->createMock(AuthorizeNetSDKResponse::class);
        $unsuccessfulResponse->expects($this->any())
            ->method('getMessage')
            ->willReturn('error message');

        return [
            'unsuccessful response' => [
                'responseMock' => $unsuccessfulResponse,
                'expectedException' => \LogicException::class,
                'exceptionMessage' => 'error message'
            ]
        ];
    }

    private function buildPaymentProfileDTO(
        string $profileType = CustomerPaymentProfile::TYPE_CREDITCARD
    ): PaymentProfileDTO {
        $country = (new Country('XX'))->setName('country')->setIso3Code('XXX');
        $region = (new Region('XX'))->setName('state');
        $country->addRegion($region);
        $region->setCountry($country);

        $customerProfile = $this->getEntity(CustomerProfile::class, ['id' => 1]);
        $customerProfile->setCustomerProfileId(self::CUSTOMER_PROFILE_ID);

        $customerUser = $this->getEntity(CustomerUser::class, ['id' => 22]);
        $customerUser->setEmail(self::EMAIL);
        $integration = $this->getEntity(Channel::class, ['id' => 33]);

        $customerProfile->setCustomerUser($customerUser);
        $customerProfile->setIntegration($integration);

        $paymentProfile = new CustomerPaymentProfile($profileType);
        $paymentProfile->setCustomerProfile($customerProfile);
        $paymentProfile->setCustomerPaymentProfileId(self::PAYMENT_PROFILE_ID);
        $paymentProfile->setDefault(true);
        $paymentProfile->setLastDigits('1234');

        $encodedData = new PaymentProfileEncodedDataDTO();
        $encodedData->setDescriptor('data_descriptor');
        $encodedData->setValue('data_value');

        $addressData = new PaymentProfileAddressDTO();
        $addressData->setFirstName('first name');
        $addressData->setLastName('last name');
        $addressData->setCompany('company');
        $addressData->setStreet('street address');
        $addressData->setCountry($country);
        $addressData->setRegion($region);
        $addressData->setZip('zip');
        $addressData->setCity('city');
        $addressData->setPhoneNumber('phone number');
        $addressData->setFaxNumber('fax number');

        $maskedData = new PaymentProfileMaskedDataDTO();
        $maskedData->setRoutingNumber('XXXX4321');
        $maskedData->setAccountNumber('XXXX1234');
        $maskedData->setNameOnAccount('first last');
        $maskedData->setAccountType('account type');
        $maskedData->setBankName('bank name');

        $paymentProfileDTO = new PaymentProfileDTO($paymentProfile);
        $paymentProfileDTO->setEncodedData($encodedData);
        $paymentProfileDTO->setAddress($addressData);
        $paymentProfileDTO->setMaskedData($maskedData);

        return $paymentProfileDTO;
    }

    private function buildAuthOptions(): array
    {
        return [
            Option\ApiLoginId::API_LOGIN_ID => self::API_LOGIN_ID,
            Option\TransactionKey::TRANSACTION_KEY => self::TRANSACTION_KEY
        ];
    }

    private function buildAddressOptions(PaymentProfileAddressDTO $addressData): array
    {
        return [
            Option\Address\FirstName::FIRST_NAME => $addressData->getFirstName(),
            Option\Address\LastName::LAST_NAME => $addressData->getLastName(),
            Option\Address\Company::COMPANY => $addressData->getCompany(),
            Option\Address\City::CITY => $addressData->getCity(),
            Option\Address\Address::ADDRESS => $addressData->getStreet(),
            Option\Address\Country::COUNTRY => $addressData->getCountryCode(),
            Option\Address\State::STATE => $addressData->getRegionString(),
            Option\Address\Zip::ZIP => $addressData->getZip(),
            Option\Address\PhoneNumber::PHONE_NUMBER => $addressData->getPhoneNumber(),
            Option\Address\FaxNumber::FAX_NUMBER => $addressData->getFaxNumber()
        ];
    }
}
