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

    const CUSTOMER_PROFILE_ID = '999';
    const PAYMENT_PROFILE_ID = '888';
    const EMAIL = 'example@oroinc.com';
    const API_LOGIN_ID = 'api_login';
    const TRANSACTION_KEY = 'transaction_key';

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

    protected function setUp(): void
    {
        $requestRegistry = new RequestRegistry();
        $requestRegistry->addRequest(new Request\CreateCustomerPaymentProfileRequest());
        $requestRegistry->addRequest(new Request\UpdateCustomerPaymentProfileRequest());
        $requestRegistry->addRequest(new Request\DeleteCustomerPaymentProfileRequest());
        $requestRegistry->addRequest(new Request\CreateCustomerProfileRequest());
        $requestRegistry->addRequest(new Request\DeleteCustomerProfileRequest());
        $requestRegistry->addRequest(new Request\GetCustomerPaymentProfileRequest());
        $requestRegistry->addRequest(new Request\GetCustomerProfileRequest());

        $this->client = $this->createMock(ClientInterface::class);
        $this->gateway = new Gateway($this->client, $requestRegistry);

        $this->doctrineHelper = $this->createMock(DoctrineHelper::class);
        $this->configProvider = $this->createMock(CIMEnabledIntegrationConfigProvider::class);

        $this->configProvider
            ->expects($this->once())
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
     * @param $responseMock
     *
     * @dataProvider successfulResponseProvider
     */
    public function testCreateCustomerProfile($responseMock)
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

        $this
            ->client
            ->expects($this->once())
            ->method('send')
            ->with(Gateway::ADDRESS_SANDBOX, Request\CreateCustomerProfileRequest::REQUEST_TYPE, $options)
            ->willReturn($responseMock);

        $result = $this->requestSender->createCustomerProfile($customerProfile);
        $this->assertEquals(self::CUSTOMER_PROFILE_ID, $result);
    }

    /**
     * @param $responseMock
     * @param $expectedException
     * @param $exceptionMessage
     *
     * @dataProvider unsuccessfulResponseProvider
     */
    public function testCreateCustomerProfileException($responseMock, $expectedException, $exceptionMessage)
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

        $this
            ->client
            ->expects($this->once())
            ->method('send')
            ->with(Gateway::ADDRESS_SANDBOX, Request\CreateCustomerProfileRequest::REQUEST_TYPE, $options)
            ->willReturn($responseMock);

        $this->expectException($expectedException);
        $this->expectExceptionMessage($exceptionMessage);

        $this->requestSender->createCustomerProfile($customerProfile);
    }

    /**
     * @param $responseMock
     *
     * @dataProvider successfulResponseProvider
     */
    public function testDeleteCustomerProfile($responseMock)
    {
        $paymentProfileDTO = $this->buildPaymentProfileDTO();
        $customerProfile = $paymentProfileDTO->getProfile()->getCustomerProfile();

        $options = array_merge(
            $this->buildAuthOptions(),
            [
                Option\CustomerProfileId::CUSTOMER_PROFILE_ID => self::CUSTOMER_PROFILE_ID
            ]
        );

        $this
            ->client
            ->expects($this->once())
            ->method('send')
            ->with(Gateway::ADDRESS_SANDBOX, Request\DeleteCustomerProfileRequest::REQUEST_TYPE, $options)
            ->willReturn($responseMock);

        $result = $this->requestSender->deleteCustomerProfile($customerProfile);
        $this->assertEquals(true, $result);
    }

    /**
     * @param $responseMock
     * @param $expectedException
     * @param $exceptionMessage
     *
     * @dataProvider unsuccessfulResponseProvider
     */
    public function testDeleteCustomerProfileException($responseMock, $expectedException, $exceptionMessage)
    {
        $paymentProfileDTO = $this->buildPaymentProfileDTO();
        $customerProfile = $paymentProfileDTO->getProfile()->getCustomerProfile();

        $options = array_merge(
            $this->buildAuthOptions(),
            [
                Option\CustomerProfileId::CUSTOMER_PROFILE_ID => self::CUSTOMER_PROFILE_ID
            ]
        );

        $this
            ->client
            ->expects($this->once())
            ->method('send')
            ->with(Gateway::ADDRESS_SANDBOX, Request\DeleteCustomerProfileRequest::REQUEST_TYPE, $options)
            ->willReturn($responseMock);

        $this->expectException($expectedException);
        $this->expectExceptionMessage($exceptionMessage);

        $this->requestSender->deleteCustomerProfile($customerProfile);
    }

    /**
     * @param $responseMock
     *
     * @dataProvider successfulResponseProvider
     */
    public function testDeleteCustomerPaymentProfile($responseMock)
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

        $this
            ->client
            ->expects($this->once())
            ->method('send')
            ->with(Gateway::ADDRESS_SANDBOX, Request\DeleteCustomerPaymentProfileRequest::REQUEST_TYPE, $options)
            ->willReturn($responseMock);

        $result = $this->requestSender->deleteCustomerPaymentProfile($paymentProfile);
        $this->assertEquals(true, $result);
    }

    /**
     * @param $responseMock
     * @param $expectedException
     * @param $exceptionMessage
     *
     * @dataProvider unsuccessfulResponseProvider
     */
    public function testDeleteCustomerPaymentProfileException($responseMock, $expectedException, $exceptionMessage)
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

        $this
            ->client
            ->expects($this->once())
            ->method('send')
            ->with(Gateway::ADDRESS_SANDBOX, Request\DeleteCustomerPaymentProfileRequest::REQUEST_TYPE, $options)
            ->willReturn($responseMock);

        $this->expectException($expectedException);
        $this->expectExceptionMessage($exceptionMessage);

        $this->requestSender->deleteCustomerPaymentProfile($paymentProfile);
    }

    /**
     * @param $responseMock
     *
     * @dataProvider successfulResponseProvider
     */
    public function testGetCustomerPaymentProfile($responseMock)
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

        $this
            ->client
            ->expects($this->once())
            ->method('send')
            ->with(Gateway::ADDRESS_SANDBOX, Request\GetCustomerPaymentProfileRequest::REQUEST_TYPE, $options)
            ->willReturn($responseMock);

        $result = $this->requestSender->getCustomerPaymentProfile($paymentProfile);
        $this->assertEquals(self::$paymentProfileData, $result);
    }

    /**
     * @param $responseMock
     * @param $expectedException
     * @param $exceptionMessage
     *
     * @dataProvider unsuccessfulResponseProvider
     */
    public function testGetCustomerPaymentProfileException($responseMock, $expectedException, $exceptionMessage)
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

        $this
            ->client
            ->expects($this->once())
            ->method('send')
            ->with(Gateway::ADDRESS_SANDBOX, Request\GetCustomerPaymentProfileRequest::REQUEST_TYPE, $options)
            ->willReturn($responseMock);

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

        $responseMock = $this->createMock(AuthorizeNetSDKResponse::class);
        $responseMock->method('isSuccessful')->willReturn(true);
        $responseMock->method('getData')->willReturn($data);

        $this
            ->client
            ->expects($this->once())
            ->method('send')
            ->with(Gateway::ADDRESS_SANDBOX, Request\GetCustomerProfileRequest::REQUEST_TYPE, $options)
            ->willReturn($responseMock);

        $result = $this->requestSender->getCustomerProfile($customerProfile);
        $this->assertEquals($paymentProfilesData, $result);
    }

    /**
     * @param $responseMock
     * @param $expectedException
     * @param $exceptionMessage
     *
     * @dataProvider unsuccessfulResponseProvider
     */
    public function testGetCustomerProfileException($responseMock, $expectedException, $exceptionMessage)
    {
        $paymentProfileDTO = $this->buildPaymentProfileDTO();
        $customerProfile = $paymentProfileDTO->getProfile()->getCustomerProfile();

        $options = array_merge(
            $this->buildAuthOptions(),
            [
                Option\CustomerProfileId::CUSTOMER_PROFILE_ID => self::CUSTOMER_PROFILE_ID
            ]
        );

        $this
            ->client
            ->expects($this->once())
            ->method('send')
            ->with(Gateway::ADDRESS_SANDBOX, Request\GetCustomerProfileRequest::REQUEST_TYPE, $options)
            ->willReturn($responseMock);

        $result = $this->requestSender->getCustomerProfile($customerProfile);
        $this->assertSame([], $result);
    }

    /**
     * @param $responseMock
     *
     * @dataProvider successfulResponseProvider
     */
    public function testCreateCustomerPaymentProfile($responseMock)
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

        $this
            ->client
            ->expects($this->once())
            ->method('send')
            ->with(Gateway::ADDRESS_SANDBOX, Request\CreateCustomerPaymentProfileRequest::REQUEST_TYPE, $options)
            ->willReturn($responseMock);

        $result = $this->requestSender->createCustomerPaymentProfile($paymentProfileDTO);
        $this->assertEquals(self::PAYMENT_PROFILE_ID, $result);
    }

    /**
     * @param $responseMock
     * @param $expectedException
     * @param $exceptionMessage
     *
     * @dataProvider unsuccessfulResponseProvider
     */
    public function testCreateCustomerPaymentProfileException($responseMock, $expectedException, $exceptionMessage)
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

        $this
            ->client
            ->expects($this->once())
            ->method('send')
            ->with(Gateway::ADDRESS_SANDBOX, Request\CreateCustomerPaymentProfileRequest::REQUEST_TYPE, $options)
            ->willReturn($responseMock);

        $this->expectException($expectedException);
        $this->expectExceptionMessage($exceptionMessage);

        $this->requestSender->createCustomerPaymentProfile($paymentProfileDTO);
    }

    /**
     * @param $responseMock
     *
     * @dataProvider successfulResponseProvider
     */
    public function testUpdateCustomerPaymentProfileNoCreditCardUpdate($responseMock)
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

        $this
            ->client
            ->expects($this->once())
            ->method('send')
            ->with(Gateway::ADDRESS_SANDBOX, Request\UpdateCustomerPaymentProfileRequest::REQUEST_TYPE, $options)
            ->willReturn($responseMock);

        $result = $this->requestSender->updateCustomerPaymentProfile($paymentProfileDTO);
        $this->assertEquals(true, $result);
    }

    /**
     * @param $responseMock
     * @param $expectedException
     * @param $exceptionMessage
     *
     * @dataProvider unsuccessfulResponseProvider
     */
    public function testUpdateCustomerPaymentProfileNoCreditCardUpdateException(
        $responseMock,
        $expectedException,
        $exceptionMessage
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

        $this
            ->client
            ->expects($this->once())
            ->method('send')
            ->with(Gateway::ADDRESS_SANDBOX, Request\UpdateCustomerPaymentProfileRequest::REQUEST_TYPE, $options)
            ->willReturn($responseMock);

        $this->expectException($expectedException);
        $this->expectExceptionMessage($exceptionMessage);

        $this->requestSender->updateCustomerPaymentProfile($paymentProfileDTO);
    }

    /**
     * @param $responseMock
     *
     * @dataProvider successfulResponseProvider
     */
    public function testUpdateCustomerPaymentProfileWithCreditCardUpdate($responseMock)
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

        $this
            ->client
            ->expects($this->once())
            ->method('send')
            ->with(Gateway::ADDRESS_SANDBOX, Request\UpdateCustomerPaymentProfileRequest::REQUEST_TYPE, $options)
            ->willReturn($responseMock);

        $result = $this->requestSender->updateCustomerPaymentProfile($paymentProfileDTO);
        $this->assertEquals(true, $result);
    }

    /**
     * @param $responseMock
     * @param $expectedException
     * @param $exceptionMessage
     *
     * @dataProvider unsuccessfulResponseProvider
     */
    public function testUpdateCustomerPaymentProfileWithCreditCardUpdateException(
        $responseMock,
        $expectedException,
        $exceptionMessage
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

        $this
            ->client
            ->expects($this->once())
            ->method('send')
            ->with(Gateway::ADDRESS_SANDBOX, Request\UpdateCustomerPaymentProfileRequest::REQUEST_TYPE, $options)
            ->willReturn($responseMock);

        $this->expectException($expectedException);
        $this->expectExceptionMessage($exceptionMessage);

        $this->requestSender->updateCustomerPaymentProfile($paymentProfileDTO);
    }

    /**
     * @param $responseMock
     *
     * @dataProvider successfulResponseProvider
     */
    public function testGetPaymentProfileAddressDTO($responseMock)
    {
        $paymentProfileDTO = $this->buildPaymentProfileDTO();
        $paymentProfile = $paymentProfileDTO->getProfile();
        $addressData = $paymentProfileDTO->getAddress();

        $countryRepository = $this->createMock(EntityRepository::class);
        $countryRepository->method('findOneBy')->willReturn($addressData->getCountry());

        $regionRepository = $this->createMock(EntityRepository::class);
        $regionRepository->method('findOneBy')->willReturn($addressData->getRegion());

        $this
            ->doctrineHelper
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

        $this
            ->client
            ->expects($this->once())
            ->method('send')
            ->with(Gateway::ADDRESS_SANDBOX, Request\GetCustomerPaymentProfileRequest::REQUEST_TYPE, $options)
            ->willReturn($responseMock);

        $paymentProfileData = $this->requestSender->getCustomerPaymentProfile($paymentProfile);

        $result = $this->requestSender->getPaymentProfileAddressDTO($paymentProfileData['bill_to']);
        $this->assertEquals($addressData, $result);
    }

    /**
     * @param $responseMock
     *
     * @dataProvider successfulResponseProvider
     */
    public function testGetPaymentProfileMaskedDataDTO($responseMock)
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

        $this
            ->client
            ->expects($this->once())
            ->method('send')
            ->with(Gateway::ADDRESS_SANDBOX, Request\GetCustomerPaymentProfileRequest::REQUEST_TYPE, $options)
            ->willReturn($responseMock);

        $paymentProfileData = $this->requestSender->getCustomerPaymentProfile($paymentProfile);

        $result = $this->requestSender->getPaymentProfileMaskedDataDTO($paymentProfileData['payment']);
        $this->assertEquals($maskedData, $result);
    }

    /**
     * @param $responseMock
     *
     * @dataProvider successfulResponseProvider
     */
    public function testUpdateCustomerPaymentProfileNoBankAccountUpdate($responseMock)
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

        $this
            ->client
            ->expects($this->once())
            ->method('send')
            ->with(Gateway::ADDRESS_SANDBOX, Request\UpdateCustomerPaymentProfileRequest::REQUEST_TYPE, $options)
            ->willReturn($responseMock);

        $result = $this->requestSender->updateCustomerPaymentProfile($paymentProfileDTO);
        $this->assertEquals(true, $result);
    }

    /**
     * @return array
     */
    public function successfulResponseProvider()
    {
        $universalData = [
            'customer_profile_id' => self::CUSTOMER_PROFILE_ID,
            'customer_payment_profile_id' => self::PAYMENT_PROFILE_ID,
            'payment_profile' => self::$paymentProfileData
        ];

        $successfulResponse = $this->createMock(AuthorizeNetSDKResponse::class);
        $successfulResponse->method('isSuccessful')->willReturn(true);
        $successfulResponse->method('getData')->willReturn($universalData);

        return [
            'successful response' => [
                'responseMock' => $successfulResponse,
            ]
        ];
    }

    /**
     * @return array
     */
    public function unsuccessfulResponseProvider()
    {
        $unsuccessfulResponse = $this->createMock(AuthorizeNetSDKResponse::class);
        $unsuccessfulResponse->method('getMessage')->willReturn('error message');

        return [
            'unsuccessful response' => [
                'responseMock' => $unsuccessfulResponse,
                'expectedException' => \LogicException::class,
                'exceptionMessage' => 'error message'
            ]
        ];
    }

    /**
     * @param string $profileType
     * @return PaymentProfileDTO
     */
    private function buildPaymentProfileDTO($profileType = CustomerPaymentProfile::TYPE_CREDITCARD)
    {
        $country = (new Country('XX'))->setName('country')->setIso3Code('XXX');
        $region = (new Region('XX'))->setName('state');
        $country->addRegion($region);
        $region->setCountry($country);

        $customerProfile = $this->getEntity(CustomerProfile::class, ['id' => 1]);
        $customerProfile->setCustomerProfileId(self::CUSTOMER_PROFILE_ID);

        /** @var CustomerUser $customerUser */
        $customerUser = $this->getEntity(CustomerUser::class, ['id' => 22]);
        $customerUser->setEmail(self::EMAIL);
        /** @var Channel $integration */
        $integration = $this->getEntity(Channel::class, ['id' => 33]);
        /** @var CustomerProfile $customerProfile */

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

    /**
     * @return array
     */
    private function buildAuthOptions()
    {
        return [
            Option\ApiLoginId::API_LOGIN_ID => self::API_LOGIN_ID,
            Option\TransactionKey::TRANSACTION_KEY => self::TRANSACTION_KEY
        ];
    }

    /**
     * @param PaymentProfileAddressDTO $addressData
     * @return array
     */
    private function buildAddressOptions(PaymentProfileAddressDTO $addressData)
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
