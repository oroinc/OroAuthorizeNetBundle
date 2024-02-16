<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Client\RequestConfigurator;

use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\contract\v1\CustomerAddressType;
use net\authorize\api\contract\v1\CustomerDataType;
use net\authorize\api\contract\v1\NameAndAddressType;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Client\RequestConfigurator\TransactionRequestConfigurator;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;
use Oro\Bundle\OrderBundle\Entity\OrderLineItem;

class TransactionRequestConfiguratorTest extends \PHPUnit\Framework\TestCase
{
    private const SOLUTION_ID = 'AAA000001';

    private TransactionRequestConfigurator $transactionRequestConfigurator;

    protected function setUp(): void
    {
        $this->transactionRequestConfigurator = new TransactionRequestConfigurator();
    }

    public function testIsApplicable()
    {
        $transactionRequest = new AnetAPI\CreateTransactionRequest();
        $createCustomerProfileRequest = new AnetAPI\CreateCustomerProfileRequest();

        $this->assertTrue($this->transactionRequestConfigurator->isApplicable($transactionRequest, []));
        $this->assertFalse($this->transactionRequestConfigurator->isApplicable($createCustomerProfileRequest, []));
    }

    /**
     * @dataProvider handleProvider
     */
    public function testHandle(array $options, AnetAPI\TransactionRequestType $transactionRequestType)
    {
        $request = new AnetAPI\CreateTransactionRequest();

        $customOptions = ['some_another_options' => 'value'];
        $options = array_merge($options, $customOptions);

        $this->transactionRequestConfigurator->handle($request, $options);

        // Configurator options removed, options that are not related to this configurator left
        $this->assertSame($customOptions, $options);
        $this->assertEquals($transactionRequestType, $request->getTransactionRequest());
    }

    public function handleProvider(): array
    {
        $opaqueData = (new AnetAPI\OpaqueDataType())
            ->setDataDescriptor('data_desc')
            ->setDataValue('data_value');
        $paymentType = (new AnetAPI\PaymentType())->setOpaqueData($opaqueData);
        $solutionType = (new AnetApi\SolutionType())->setId(self::SOLUTION_ID);

        return array_merge(
            [
                'amount only' => [
                    'options' => [
                        Option\Amount::AMOUNT => 1.00,
                    ],
                    'transactionRequestType' => (new AnetAPI\TransactionRequestType())
                        ->setAmount(1.00),
                ],
                'transaction type only' => [
                    'options' => [
                        Option\Transaction::TRANSACTION_TYPE => 'transaction',
                    ],
                    'transactionRequestType' => (new AnetAPI\TransactionRequestType())
                        ->setTransactionType('transaction'),
                ],
                'currency only' => [
                    'options' => [
                        Option\Currency::CURRENCY => 'USD',
                    ],
                    'transactionRequestType' => (new AnetAPI\TransactionRequestType())
                        ->setCurrencyCode('USD'),
                ],
                'original transaction only' => [
                    'options' => [
                        Option\OriginalTransaction::ORIGINAL_TRANSACTION => 'ref',
                    ],
                    'transactionRequestType' => (new AnetAPI\TransactionRequestType())
                        ->setRefTransId('ref'),
                ],
                'with_customer_profile_id' => [
                    'options' => [
                        Option\CustomerProfileId::CUSTOMER_PROFILE_ID => '777'
                    ],
                    'transactionRequestType' => (new AnetAPI\TransactionRequestType())
                        ->setProfile((new AnetAPI\CustomerProfilePaymentType())->setCustomerProfileId('777'))
                ],
            ],
            $this->getOpaqueHandleData($paymentType),
            $this->getCustomerPaymentProfileHandleData(),
            $this->getCreateProfileCustomerProfileExistsData(),
            $this->getCreateProfileGenerateCustomerIdHandleData(),
            $this->getAllChargeCreditCardHandleData($paymentType, $solutionType),
            $this->getAllChargeCustomerProfileHandleData($solutionType),
            $this->getBillingAddressHandleData(),
            $this->getShippingAddressHandleData(),
            $this->getInvoiceNumberHandleData(),
            $this->getTaxAmountHandleData(),
            $this->getLineItemsHandleData()
        );
    }

    private function getOpaqueHandleData(AnetAPI\PaymentType $paymentType): array
    {
        return [
            'opaque parameters only' => [
                'options' => [
                    Option\DataDescriptor::DATA_DESCRIPTOR => 'data_desc',
                    Option\DataValue::DATA_VALUE => 'data_value',
                ],
                'transactionRequestType' => (new AnetAPI\TransactionRequestType())
                    ->setPayment($paymentType),
            ],
            'opaque parameters only(only desc)' => [
                'options' => [
                    Option\DataDescriptor::DATA_DESCRIPTOR => 'data_desc',
                ],
                'transactionRequestType' => new AnetAPI\TransactionRequestType(),
            ],
            'opaque parameters only(only valye)' => [
                'options' => [
                    Option\DataValue::DATA_VALUE => 'data_value',
                ],
                'transactionRequestType' => new AnetAPI\TransactionRequestType(),
            ],
        ];
    }

    private function getCustomerPaymentProfileHandleData(): array
    {
        return [
            'with_customer_payment_profile_id' => [
                'options' => [
                    Option\ChargeType::NAME => Option\ChargeType::TYPE_PAYMENT_PROFILE,
                    Option\CustomerProfileId::CUSTOMER_PROFILE_ID => '777',
                    Option\CustomerPaymentProfileId::CUSTOMER_PAYMENT_PROFILE_ID => '888'
                ],
                'transactionRequestType' => (new AnetAPI\TransactionRequestType())
                    ->setProfile(
                        (new AnetAPI\CustomerProfilePaymentType())
                            ->setCustomerProfileId('777')
                            ->setPaymentProfile(
                                (new AnetAPI\PaymentProfileType())->setPaymentProfileId('888')
                            )
                    )
            ],
            'with_customer_payment_profile_id_and_card_code' => [
                'options' => [
                    Option\ChargeType::NAME => Option\ChargeType::TYPE_PAYMENT_PROFILE,
                    Option\CustomerProfileId::CUSTOMER_PROFILE_ID => '777',
                    Option\CustomerPaymentProfileId::CUSTOMER_PAYMENT_PROFILE_ID => '999',
                    Option\CardCode::NAME => '0777 7770'
                ],
                'transactionRequestType' => (new AnetAPI\TransactionRequestType())
                    ->setProfile(
                        (new AnetAPI\CustomerProfilePaymentType())
                            ->setCustomerProfileId('777')
                            ->setPaymentProfile(
                                (new AnetAPI\PaymentProfileType())
                                    ->setPaymentProfileId('999')
                                    ->setCardCode('0777 7770')
                            )
                    )
            ],
        ];
    }

    private function getCreateProfileCustomerProfileExistsData(): array
    {
        return [
            'with_create_profile_true' => [
                'options' => [
                    Option\CustomerProfileId::CUSTOMER_PROFILE_ID => '777',
                    Option\CreateProfile::NAME => true
                ],
                'transactionRequestType' => (new AnetAPI\TransactionRequestType())
                    ->setProfile(
                        (new AnetAPI\CustomerProfilePaymentType())
                            ->setCustomerProfileId('777')
                            ->setCreateProfile(true)
                    )
            ],
            'with_create_profile_false' => [
                'options' => [
                    Option\CustomerProfileId::CUSTOMER_PROFILE_ID => '777',
                    Option\CreateProfile::NAME => false
                ],
                'transactionRequestType' => (new AnetAPI\TransactionRequestType())
                    ->setProfile(
                        (new AnetAPI\CustomerProfilePaymentType())->setCustomerProfileId('777')
                    )
            ],
        ];
    }

    private function getCreateProfileGenerateCustomerIdHandleData(): array
    {
        return [
            'with_create_profile_customer_data_no_email' => [
                'options' => [
                    Option\CreateProfile::NAME => true,
                    Option\ChargeType::NAME => Option\ChargeType::TYPE_CREDIT_CARD,
                    Option\CustomerDataId::NAME => 'oro-4-101'
                ],
                'transactionRequestType' => (new AnetAPI\TransactionRequestType())
                    ->setCustomer(
                        (new CustomerDataType())->setId('oro-4-101')
                    )
                    ->setProfile(
                        (new AnetAPI\CustomerProfilePaymentType())->setCreateProfile(true)
                    )
            ],
            'with_create_profile_customer_data_with_email' => [
                'options' => [
                    Option\CreateProfile::NAME => true,
                    Option\ChargeType::NAME => Option\ChargeType::TYPE_CREDIT_CARD,
                    Option\CustomerDataId::NAME => 'oro-4-101',
                    Option\Email::EMAIL => 'test@ggmail.com'
                ],
                'transactionRequestType' => (new AnetAPI\TransactionRequestType())
                    ->setCustomer(
                        (new CustomerDataType())->setId('oro-4-101')->setEmail('test@ggmail.com')
                    )
                    ->setProfile(
                        (new AnetAPI\CustomerProfilePaymentType())->setCreateProfile(true)
                    )
            ],
        ];
    }

    private function getBillingAddressHandleData(): array
    {
        return [
            'billTo hasOneField' => [
                'options' => [
                    Option\ChargeType::NAME => Option\ChargeType::TYPE_CREDIT_CARD,
                    Option\Address\FirstName::FIRST_NAME => 'John Doe'
                ],
                'transactionRequestType' => (new AnetAPI\TransactionRequestType())
                    ->setBillTo(
                        (new CustomerAddressType())->setFirstName('John Doe')
                    )
            ],
            'billTo hasSomeFields' => [
                'options' => [
                    Option\ChargeType::NAME => Option\ChargeType::TYPE_CREDIT_CARD,
                    //
                    Option\Address\City::CITY => 'New York',
                    Option\Address\Zip::ZIP => '01001',
                    Option\Address\Country::COUNTRY => 'USA',
                    Option\Address\PhoneNumber::PHONE_NUMBER => '+380991111111',
                ],
                'transactionRequestType' => (new AnetAPI\TransactionRequestType())
                    ->setBillTo(
                        (new CustomerAddressType())
                            ->setCity('New York')
                            ->setZip('01001')
                            ->setCountry('USA')
                            ->setPhoneNumber('+380991111111')
                    )
            ],
            'billTo hasNone' => [
                'options' => [
                    Option\ChargeType::NAME => Option\ChargeType::TYPE_CREDIT_CARD
                ],
                'transactionRequestType' => new AnetAPI\TransactionRequestType()
            ]
        ];
    }

    private function getAllChargeCreditCardHandleData(
        AnetAPI\PaymentType $paymentType,
        AnetApi\SolutionType $solutionType
    ): array {
        return [
            'all parameters together charge credit card' => [
                'options' => [
                    Option\ChargeType::NAME => Option\ChargeType::TYPE_CREDIT_CARD,
                    Option\DataDescriptor::DATA_DESCRIPTOR => 'data_desc',
                    Option\DataValue::DATA_VALUE => 'data_value',
                    Option\Amount::AMOUNT => 1.00,
                    Option\Transaction::TRANSACTION_TYPE => 'transaction',
                    Option\Currency::CURRENCY => 'USD',
                    Option\OriginalTransaction::ORIGINAL_TRANSACTION => 'ref',
                    Option\SolutionId::SOLUTION_ID => self::SOLUTION_ID
                ],
                'transactionRequestType' => (new AnetAPI\TransactionRequestType())
                    ->setPayment($paymentType)
                    ->setAmount(1.00)
                    ->setTransactionType('transaction')
                    ->setCurrencyCode('USD')
                    ->setRefTransId('ref')
                    ->setSolution($solutionType)
            ]
        ];
    }

    private function getAllChargeCustomerProfileHandleData(AnetApi\SolutionType $solutionType): array
    {
        return [
            'all parameters together charge payment profile' => [
                'options' => [
                    Option\ChargeType::NAME => Option\ChargeType::TYPE_PAYMENT_PROFILE,
                    Option\Amount::AMOUNT => 1.00,
                    Option\Transaction::TRANSACTION_TYPE => 'transaction',
                    Option\Currency::CURRENCY => 'USD',
                    Option\OriginalTransaction::ORIGINAL_TRANSACTION => 'ref',
                    Option\SolutionId::SOLUTION_ID => self::SOLUTION_ID,
                    Option\CustomerProfileId::CUSTOMER_PROFILE_ID => '777',
                    Option\CustomerPaymentProfileId::CUSTOMER_PAYMENT_PROFILE_ID => '888',
                    Option\CardCode::NAME => '0777 7770'
                ],
                'transactionRequestType' => (new AnetAPI\TransactionRequestType())
                    ->setAmount(1.00)
                    ->setTransactionType('transaction')
                    ->setCurrencyCode('USD')
                    ->setRefTransId('ref')
                    ->setSolution($solutionType)
                    ->setProfile(
                        (new AnetAPI\CustomerProfilePaymentType())
                            ->setCustomerProfileId('777')
                            ->setPaymentProfile(
                                (new AnetAPI\PaymentProfileType())
                                    ->setPaymentProfileId('888')
                                    ->setCardCode('0777 7770')
                            )
                    )
            ],
        ];
    }

    private function getShippingAddressHandleData(): array
    {
        return [
            'shipTo hasOneField' => [
                'options' => [
                    Option\ShippingAddress::FIRST_NAME => 'John Doe'
                ],
                'transactionRequestType' => (new AnetAPI\TransactionRequestType())
                    ->setShipTo(
                        (new NameAndAddressType())
                            ->setFirstName('John Doe')
                    )
            ],
            'shipTo hasSomeFields' => [
                'options' => [
                    Option\ShippingAddress::CITY => 'New York',
                    Option\ShippingAddress::ZIP => '01001',
                    Option\ShippingAddress::COUNTRY => 'USA',
                ],
                'transactionRequestType' => (new AnetAPI\TransactionRequestType())
                    ->setShipTo(
                        (new NameAndAddressType())
                            ->setCity('New York')
                            ->setZip('01001')
                            ->setCountry('USA')
                    )
            ],
            'shipTo hasAllFields' => [
                'options' => [
                    Option\ShippingAddress::FIRST_NAME => 'John',
                    Option\ShippingAddress::LAST_NAME => 'Doe',
                    Option\ShippingAddress::COMPANY => 'ORO',
                    Option\ShippingAddress::ADDRESS => 'street',
                    Option\ShippingAddress::CITY => 'city',
                    Option\ShippingAddress::STATE => 'NJ',
                    Option\ShippingAddress::ZIP => '12345',
                    Option\ShippingAddress::COUNTRY => 'USA'
                ],
                'transactionRequestType' => (new AnetAPI\TransactionRequestType())
                    ->setShipTo(
                        (new NameAndAddressType())
                            ->setFirstName('John')
                            ->setLastName('Doe')
                            ->setCompany('ORO')
                            ->setAddress('street')
                            ->setCity('city')
                            ->setState('NJ')
                            ->setZip('12345')
                            ->setCountry('USA')
                    )
            ],
        ];
    }

    private function getInvoiceNumberHandleData(): array
    {
        return [
            'with invoiceNumber' => [
                'options' => [
                    Option\ChargeType::NAME => Option\ChargeType::TYPE_CREDIT_CARD,
                    Option\InvoiceNumber::NAME => 'INVOICE_NUMBER'
                ],
                'transactionRequestType' => (new AnetAPI\TransactionRequestType())
                    ->setOrder(
                        (new AnetAPI\OrderType())
                            ->setInvoiceNumber('INVOICE_NUMBER')
                    )
            ],
            'without invoiceNumber' => [
                'options' => [
                    Option\ChargeType::NAME => Option\ChargeType::TYPE_CREDIT_CARD
                ],
                'transactionRequestType' => new AnetAPI\TransactionRequestType()
            ]
        ];
    }

    private function getTaxAmountHandleData(): array
    {
        return [
            'with taxAmount' => [
                'options' => [
                    Option\ChargeType::NAME => Option\ChargeType::TYPE_CREDIT_CARD,
                    Option\TaxAmount::NAME => 9.99
                ],
                'transactionRequestType' => (new AnetAPI\TransactionRequestType())
                    ->setTax(
                        (new AnetAPI\ExtendedAmountType())
                            ->setAmount(9.99)
                    )
            ],
            'zero taxAmount' => [
                'options' => [
                    Option\ChargeType::NAME => Option\ChargeType::TYPE_CREDIT_CARD,
                    Option\TaxAmount::NAME => 0
                ],
                'transactionRequestType' => new AnetAPI\TransactionRequestType()
            ]
        ];
    }

    private function getLineItemsHandleData(): array
    {
        $repearString = function ($multiplier) {
            return \str_repeat('-', $multiplier);
        };

        $subString = function ($string, $length) {
            return \mb_substr($string, 0, $length);
        };

        $lineItem1 = new OrderLineItem();
        $lineItem1->setProductSku('SKU1');
        $lineItem1->setProductName('PRODUCT1');
        $lineItem1->setQuantity(1);
        $lineItem1->setValue(9.99);

        $lineItem2 = new OrderLineItem();
        $lineItem2->setProductSku('SKU2' . $repearString(50));
        $lineItem2->setProductName('PRODUCT2' .  $repearString(300));
        $lineItem2->setQuantity(2);
        $lineItem2->setValue(20);

        $lineItem3 = new OrderLineItem();
        $lineItem3->setProductSku('SKU3');
        $lineItem3->setProductName('');
        $lineItem3->setFreeFormProduct('SKU3FREEFORM');
        $lineItem3->setQuantity(3);
        $lineItem3->setValue(10);
        return [
            'with lineItems' => [
                'options' => [
                    Option\ChargeType::NAME => Option\ChargeType::TYPE_CREDIT_CARD,
                    Option\LineItems::NAME => [$lineItem1, $lineItem2, $lineItem3]
                ],
                'transactionRequestType' => (new AnetAPI\TransactionRequestType())
                    ->setLineItems([
                        (new AnetAPI\LineItemType())
                            ->setItemId($lineItem1->getProductSku())
                            ->setName($lineItem1->getProductName())
                            ->setDescription(null)
                            ->setQuantity($lineItem1->getQuantity())
                            ->setUnitPrice($lineItem1->getValue()),
                        (new AnetAPI\LineItemType())
                            ->setItemId($subString($lineItem2->getProductSku(), 31))
                            ->setName($subString($lineItem2->getProductName(), 31))
                            ->setDescription($subString($lineItem2->getProductName(), 255))
                            ->setQuantity($lineItem2->getQuantity())
                            ->setUnitPrice($lineItem2->getValue()),
                        (new AnetAPI\LineItemType())
                            ->setItemId($lineItem3->getProductSku())
                            ->setName($lineItem3->getFreeFormProduct())
                            ->setDescription(null)
                            ->setQuantity($lineItem3->getQuantity())
                            ->setUnitPrice($lineItem3->getValue()),
                    ])
            ],
            'without lineItems' => [
                'options' => [
                    Option\ChargeType::NAME => Option\ChargeType::TYPE_CREDIT_CARD
                ],
                'transactionRequestType' => new AnetAPI\TransactionRequestType()
            ]
        ];
    }
}
