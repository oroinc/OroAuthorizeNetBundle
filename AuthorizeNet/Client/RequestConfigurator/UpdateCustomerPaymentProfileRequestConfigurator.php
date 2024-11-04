<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Client\RequestConfigurator;

use net\authorize\api\contract\v1 as AnetAPI;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

/**
 * request configurator for updateCustomerPaymentProfile request
 */
class UpdateCustomerPaymentProfileRequestConfigurator extends CreateCustomerPaymentProfileRequestConfigurator
{
    #[\Override]
    public function isApplicable(AnetAPI\ANetApiRequestType $request, array $options)
    {
        return $request instanceof AnetAPI\UpdateCustomerPaymentProfileRequest;
    }

    /**
     * @param AnetAPI\ANetApiRequestType|AnetAPI\UpdateCustomerPaymentProfileRequest $request
     * @param array $options
     */
    #[\Override]
    public function handle(AnetAPI\ANetApiRequestType $request, array &$options)
    {
        parent::handle($request, $options);

        $paymentProfile = $request->getPaymentProfile();

        if (array_key_exists(Option\CustomerPaymentProfileId::CUSTOMER_PAYMENT_PROFILE_ID, $options)) {
            $paymentProfile
                ->setCustomerPaymentProfileId($options[Option\CustomerPaymentProfileId::CUSTOMER_PAYMENT_PROFILE_ID]);
        }

        $updatePaymentData = $options[Option\UpdatePaymentData::UPDATE_PAYMENT_DATA] ?? null;
        $profileType = $options[Option\ProfileType::PROFILE_TYPE] ?? Option\ProfileType::CREDITCARD_TYPE;
        if ($updatePaymentData === false) {
            if ($profileType === Option\ProfileType::CREDITCARD_TYPE) {
                $paymentProfile->setPayment($this->getPaymentTypeWithCreditCardData($options));
            }
            if ($profileType === Option\ProfileType::ECHECK_TYPE) {
                $paymentProfile->setPayment($this->getPaymentTypeWithBankAccountData($options));
            }
        }

        // Remove handled options to prevent handling in fallback configurator
        unset(
            $options[Option\CustomerPaymentProfileId::CUSTOMER_PAYMENT_PROFILE_ID],
            $options[Option\UpdatePaymentData::UPDATE_PAYMENT_DATA],
            $options[Option\ProfileType::PROFILE_TYPE],
            $options[Option\CardNumber::CARD_NUMBER],
            $options[Option\ExpirationDate::EXPIRATION_DATE],
            $options[Option\AccountNumber::ACCOUNT_NUMBER],
            $options[Option\RoutingNumber::ROUTING_NUMBER],
            $options[Option\NameOnAccount::NAME_ON_ACCOUNT],
            $options[Option\AccountType::ACCOUNT_TYPE],
            $options[Option\BankName::BANK_NAME]
        );
    }

    /**
     * @return AnetAPI\CustomerPaymentProfileExType
     */
    #[\Override]
    protected function createCustomerPaymentProfile()
    {
        return new AnetAPI\CustomerPaymentProfileExType();
    }

    /**
     * @param array $options
     * @return AnetAPI\PaymentType
     */
    protected function getPaymentTypeWithCreditCardData(array $options)
    {
        $creditCardType = new AnetAPI\CreditCardType();

        if (array_key_exists(Option\CardNumber::CARD_NUMBER, $options)) {
            $creditCardType->setCardNumber($options[Option\CardNumber::CARD_NUMBER]);
        }

        if (array_key_exists(Option\ExpirationDate::EXPIRATION_DATE, $options)) {
            $creditCardType->setExpirationDate($options[Option\ExpirationDate::EXPIRATION_DATE]);
        }

        $paymentType = new AnetAPI\PaymentType();
        $paymentType->setCreditCard($creditCardType);

        return $paymentType;
    }

    /**
     * @param array $options
     * @return AnetAPI\PaymentType
     */
    protected function getPaymentTypeWithBankAccountData(array $options)
    {
        $bankAccountType = new AnetAPI\BankAccountType();

        if (array_key_exists(Option\AccountNumber::ACCOUNT_NUMBER, $options)) {
            $bankAccountType->setAccountNumber($options[Option\AccountNumber::ACCOUNT_NUMBER]);
        }

        if (array_key_exists(Option\RoutingNumber::ROUTING_NUMBER, $options)) {
            $bankAccountType->setRoutingNumber($options[Option\RoutingNumber::ROUTING_NUMBER]);
        }

        if (array_key_exists(Option\NameOnAccount::NAME_ON_ACCOUNT, $options)) {
            $bankAccountType->setNameOnAccount($options[Option\NameOnAccount::NAME_ON_ACCOUNT]);
        }

        if (array_key_exists(Option\AccountType::ACCOUNT_TYPE, $options)) {
            $bankAccountType->setAccountType($options[Option\AccountType::ACCOUNT_TYPE]);
        }

        if (array_key_exists(Option\BankName::BANK_NAME, $options)) {
            $bankAccountType->setBankName($options[Option\BankName::BANK_NAME]);
        }

        $paymentType = new AnetAPI\PaymentType();
        $paymentType->setBankAccount($bankAccountType);

        return $paymentType;
    }
}
