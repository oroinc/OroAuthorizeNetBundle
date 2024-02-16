<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Client\RequestConfigurator;

use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\contract\v1\CustomerAddressType;
use net\authorize\api\contract\v1\CustomerDataType;
use net\authorize\api\contract\v1\NameAndAddressType;
use net\authorize\api\contract\v1\TransactionRequestType as AnetRequest;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;
use Oro\Bundle\EntityExtendBundle\PropertyAccess;
use Oro\Bundle\OrderBundle\Entity\OrderLineItem;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * Request Configurator for transactionRequest field
 */
class TransactionRequestConfigurator implements RequestConfiguratorInterface
{
    const LINE_ITEMS_MAX_QUANTITY = 30;

    /**
     * {@inheritdoc}
     */
    public function isApplicable(AnetAPI\ANetApiRequestType $request, array $options)
    {
        return $request instanceof AnetAPI\CreateTransactionRequest;
    }

    /**
     * @param AnetAPI\ANetApiRequestType|AnetAPI\CreateTransactionRequest $request
     * @param array $options
     */
    public function handle(AnetAPI\ANetApiRequestType $request, array &$options)
    {
        $request->setTransactionRequest($this->getTransactionRequest($options));

        // Remove handled options to prevent handling in fallback configurator
        unset(
            $options[Option\DataDescriptor::DATA_DESCRIPTOR],
            $options[Option\DataValue::DATA_VALUE],
            $options[Option\Amount::AMOUNT],
            $options[Option\Transaction::TRANSACTION_TYPE],
            $options[Option\Currency::CURRENCY],
            $options[Option\OriginalTransaction::ORIGINAL_TRANSACTION],
            $options[Option\SolutionId::SOLUTION_ID],
            $options[Option\CustomerProfileId::CUSTOMER_PROFILE_ID],
            $options[Option\CustomerPaymentProfileId::CUSTOMER_PAYMENT_PROFILE_ID],
            $options[Option\ChargeType::NAME],
            $options[Option\CardCode::NAME],
            $options[Option\CreateProfile::NAME],
            $options[Option\CustomerDataId::NAME],
            $options[Option\Email::EMAIL],
            $options[Option\Address\FirstName::FIRST_NAME],
            $options[Option\Address\LastName::LAST_NAME],
            $options[Option\Address\Company::COMPANY],
            $options[Option\Address\Address::ADDRESS],
            $options[Option\Address\City::CITY],
            $options[Option\Address\State::STATE],
            $options[Option\Address\Zip::ZIP],
            $options[Option\Address\Country::COUNTRY],
            $options[Option\Address\PhoneNumber::PHONE_NUMBER],
            $options[Option\Address\FaxNumber::FAX_NUMBER],
            $options[Option\LineItems::NAME],
            $options[Option\InvoiceNumber::NAME],
            $options[Option\TaxAmount::NAME],
            $options[Option\CustomerIp::NAME]
        );
        // Remove handled options from shipping address as well
        foreach (Option\ShippingAddress::ALL_OPTION_KEYS as $shipingAddresField) {
            unset($options[$shipingAddresField]);
        }
    }

    /**
     * @param array $options
     * @return AnetRequest
     */
    protected function getTransactionRequest(array $options)
    {
        $request = new AnetRequest();
        $accessor = PropertyAccess::createPropertyAccessor();

        $transactionType = $options[Option\Transaction::TRANSACTION_TYPE] ?? null;
        $amount = $options[Option\Amount::AMOUNT] ?? null;
        $currencyCode = $options[Option\Currency::CURRENCY] ?? null;
        $refTransId = $options[Option\OriginalTransaction::ORIGINAL_TRANSACTION] ?? null;
        $customerIp = $options[Option\CustomerIp::NAME] ?? null;

        $this
            ->setAnetRequestProp($request, $accessor, 'transactionType', $transactionType)
            ->setAnetRequestProp($request, $accessor, 'amount', $amount)
            ->setAnetRequestProp($request, $accessor, 'currencyCode', $currencyCode)
            ->setAnetRequestProp($request, $accessor, 'refTransId', $refTransId)
            ->setAnetRequestProp($request, $accessor, 'customer', $this->getCustomerData($options))
            ->setAnetRequestProp($request, $accessor, 'billTo', $this->getBillTo($options))
            ->setAnetRequestProp($request, $accessor, 'payment', $this->getPaymentType($options))
            ->setAnetRequestProp($request, $accessor, 'solution', $this->getSolutionType($options))
            ->setAnetRequestProp($request, $accessor, 'profile', $this->getProfile($options))
            ->setAnetRequestProp($request, $accessor, 'shipTo', $this->getShipTo($options))
            ->setAnetRequestProp($request, $accessor, 'lineItems', $this->getLineItems($options))
            ->setAnetRequestProp($request, $accessor, 'order', $this->getOrder($options))
            ->setAnetRequestProp($request, $accessor, 'tax', $this->getTax($options))
            ->setAnetRequestProp($request, $accessor, 'customerIP', $customerIp);

        return $request;
    }

    /**
     * @param AnetRequest $request
     * @param PropertyAccessor $accessor
     * @param string $setterName
     * @param $propValue
     * @return TransactionRequestConfigurator
     */
    protected function setAnetRequestProp(
        AnetRequest $request,
        PropertyAccessor $accessor,
        string $setterName,
        $propValue
    ): self {
        if (null !== $propValue) {
            $accessor->setValue($request, $setterName, $propValue);
        }

        return $this;
    }

    /**
     * Should set In case if Charge Type credit card, and flag "createProfile" set
     * and Generated customerDataId "oro-<integration.id>-<CustomerUser.id>" is in $options
     * <customer>
     *    <id>oro-x-xxx</id>
     *    <email>email@email.com</email>
     * </customer>
     */
    protected function getCustomerData(array $options): ?CustomerDataType
    {
        $chargeType = $options[Option\ChargeType::NAME] ?? null;
        $createProfile = $options[Option\CreateProfile::NAME] ?? false;
        $customerDataId = $options[Option\CustomerDataId::NAME] ?? null;
        if (null !== $chargeType
            && Option\ChargeType::TYPE_CREDIT_CARD === $chargeType
            && true === $createProfile
            && null !== $customerDataId
        ) {
            $customerData = (new CustomerDataType())->setId($customerDataId);
            $email = $options[Option\Email::EMAIL] ?? null;
            if (null !== $email) {
                $customerData->setEmail($email);
            }

            return $customerData;
        }

        return null;
    }

    protected function getPaymentProfile(array $options): ?AnetAPI\PaymentProfileType
    {
        $chargeType = $options[Option\ChargeType::NAME] ?? null;
        $paymentPid = $options[Option\CustomerPaymentProfileId::CUSTOMER_PAYMENT_PROFILE_ID] ?? null;
        if (Option\ChargeType::TYPE_PAYMENT_PROFILE !== $chargeType || null === $paymentPid) {
            return null;
        }

        $paymentProfile = (new AnetAPI\PaymentProfileType())->setPaymentProfileId($paymentPid);
        $cardCode = $options[Option\CardCode::NAME] ?? null;
        if (null !== $cardCode) {
            $paymentProfile->setCardCode($cardCode);
        }

        return $paymentProfile;
    }

    protected function getBillTo(array $options): ?CustomerAddressType
    {
        $chargeType = $options[Option\ChargeType::NAME] ?? null;
        if (Option\ChargeType::TYPE_CREDIT_CARD !== $chargeType) {
            return null;
        }

        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $customerAddress = new CustomerAddressType();
        $optionKeys = [
            Option\Address\FirstName::FIRST_NAME,
            Option\Address\LastName::LAST_NAME,
            Option\Address\Company::COMPANY,
            Option\Address\Address::ADDRESS,
            Option\Address\City::CITY,
            Option\Address\State::STATE,
            Option\Address\Zip::ZIP,
            Option\Address\Country::COUNTRY,
            Option\Address\PhoneNumber::PHONE_NUMBER,
            Option\Address\FaxNumber::FAX_NUMBER
        ];

        $hasAddress = false;
        foreach ($optionKeys as $optionKey) {
            if (\array_key_exists($optionKey, $options)) {
                $hasAddress = true;
                $propertyAccessor->setValue($customerAddress, $optionKey, $options[$optionKey]);
            }
        }

        if ($hasAddress) {
            return $customerAddress;
        }

        return null;
    }

    protected function getShipTo(array $options): ?NameAndAddressType
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $address = new NameAndAddressType();

        $hasAddress = false;
        foreach (Option\ShippingAddress::ALL_OPTION_KEYS as $optionKey) {
            if (\array_key_exists($optionKey, $options)) {
                $hasAddress = true;
                $prefix = Option\ShippingAddress::PREFIX;
                $addressOptionKey = substr_replace($optionKey, '', 0, \strlen($prefix));

                $propertyAccessor->setValue($address, $addressOptionKey, $options[$optionKey]);
            }
        }

        if ($hasAddress) {
            return $address;
        }

        return null;
    }

    /**
     * Should set In case If Customer has no CustomerProfile and wants to create both
     *  <profile>
     *     <createProfile>true</createProfile>
     *  </profile>
     *
     * Should set In case If Customer has Oro CustomerProfile, and wants to create one at AuthorizeNet
     *  <profile>
     *      <createProfile>true</createProfile>
     *      <customerProfileId>oro-4-55</customerProfileId>
     *  </profile>
     *
     * That would be for Charge Customer Profile
     *  <profile>
     *      <customerProfileId>oro-4-55</customerProfileId>
     *      <paymentProfile>
     *         <paymentProfileId>9999</paymentProfileId>
     *         <cardCode>123</cardCode>
     *      </paymentProfile>
     *  </profile>
     */
    protected function getProfile(array $options): ?AnetAPI\CustomerProfilePaymentType
    {
        $profile = new AnetAPI\CustomerProfilePaymentType();
        $customerProfileId = $options[Option\CustomerProfileId::CUSTOMER_PROFILE_ID] ?? null;
        if ($customerProfileId) {
            $profile->setCustomerProfileId($customerProfileId);
        }

        $createProfile = $options[Option\CreateProfile::NAME] ?? false;
        if ($createProfile) {
            return $profile->setCreateProfile($createProfile);
        }

        if (!$customerProfileId) {
            return null;
        }

        $paymentProfile = $this->getPaymentProfile($options);
        if ($paymentProfile) {
            $profile->setPaymentProfile($paymentProfile);
        }

        return $profile;
    }

    protected function getPaymentType(array $options): ?AnetAPI\PaymentType
    {
        $dataDescriptor = $options[Option\DataDescriptor::DATA_DESCRIPTOR] ?? null;
        $dataValue = $options[Option\DataValue::DATA_VALUE] ?? null;
        if (null === $dataDescriptor || null === $dataValue) {
            return null;
        }

        $opaqueDataType = (new AnetAPI\OpaqueDataType())
            ->setDataDescriptor($options[Option\DataDescriptor::DATA_DESCRIPTOR])
            ->setDataValue($options[Option\DataValue::DATA_VALUE])
        ;

        return (new AnetAPI\PaymentType())->setOpaqueData($opaqueDataType);
    }

    protected function getSolutionType(array $options): ?AnetAPI\SolutionType
    {
        $solutionId = $options[Option\SolutionId::SOLUTION_ID] ?? null;
        if (null === $solutionId) {
            return null;
        }

        return (new AnetAPI\SolutionType())->setId($options[Option\SolutionId::SOLUTION_ID]);
    }

    /**
     * @param array $options
     * @return null|AnetAPI\LineItemType[]
     */
    protected function getLineItems(array $options): ?array
    {
        /** @var OrderLineItem[] $lineItems */
        $lineItems = $options[Option\LineItems::NAME] ?? [];
        $requestLineItems = null;
        $shortStringLength = 31;
        $longStringLength = 255;

        foreach (\array_slice($lineItems, 0, self::LINE_ITEMS_MAX_QUANTITY) as $item) {
            $productName = $item->getProductName() ?: $item->getFreeFormProduct();
            $requestLineItem = new AnetAPI\LineItemType();
            $requestLineItem->setItemId($this->truncateString($item->getProductSku(), $shortStringLength));
            $requestLineItem->setName($this->truncateString($productName, $shortStringLength));

            if (\mb_strlen($productName) > $shortStringLength) {
                $requestLineItem->setDescription($this->truncateString($productName, $longStringLength));
            }

            $requestLineItem->setQuantity($item->getQuantity());
            $requestLineItem->setUnitPrice($item->getValue() ?: 0);

            $requestLineItems[] = $requestLineItem;
        }

        return $requestLineItems;
    }

    /**
     * @param string $string
     * @param $length
     * @return string
     */
    protected function truncateString($string, $length)
    {
        return \mb_substr($string, 0, $length);
    }

    protected function getOrder(array $options): ?AnetAPI\OrderType
    {
        /** @var string $invoiceNumber */
        $invoiceNumber = $options[Option\InvoiceNumber::NAME] ?? null;
        $order = null;

        if ($invoiceNumber) {
            $order = new AnetAPI\OrderType();
            $order->setInvoiceNumber($this->truncateString($invoiceNumber, 20));
        }

        return $order;
    }

    protected function getTax(array $options): ?AnetAPI\ExtendedAmountType
    {
        /** @var string $taxAmount */
        $taxAmount = $options[Option\TaxAmount::NAME] ?? null;
        $tax = null;

        if ($taxAmount) {
            $tax = new AnetAPI\ExtendedAmountType();
            $tax->setAmount($taxAmount);
        }

        return $tax;
    }
}
