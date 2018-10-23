<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

/**
 * Option class to add options for opaqueData or creditCardData depends of option value (Authorize.Net SDK)
 */
class PaymentData implements OptionInterface, OptionsDependentInterface
{
    public function configureOption(OptionsResolver $resolver)
    {
        $resolver
            ->addOption(new UpdatePaymentData())
            ->addOption(new ProfileType())
            ->addOption(new DataDescriptor($isRequired = false))
            ->addOption(new DataValue($isRequired = false))
            ->addOption(new CardNumber($isRequired = false))
            ->addOption(new ExpirationDate($isRequired = false))
            ->addOption(new AccountNumber($isRequired = false))
            ->addOption(new RoutingNumber($isRequired = false))
            ->addOption(new NameOnAccount($isRequired = false))
            ->addOption(new AccountType($isRequired = false))
            ->addOption(new BankName($isRequired = false));
    }

    /**
     * {@inheritdoc}
     */
    public function isApplicableDependent(array $options)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function configureDependentOption(OptionsResolver $resolver, array $options)
    {
        $updatePaymentData = $options[UpdatePaymentData::UPDATE_PAYMENT_DATA] ?? false;
        $profileType = $options[ProfileType::PROFILE_TYPE] ?? ProfileType::CREDITCARD_TYPE;

        if ($updatePaymentData) {
            $this->addEncodedDataOptions($resolver);
            $this->removeCreditCardOptions($resolver);
            $this->removeBankAccountOptions($resolver);
        } else {
            $this->removeEncodedDataOptions($resolver);

            if ($profileType === ProfileType::CREDITCARD_TYPE) {
                $this->addCreditCardOptions($resolver);
                $this->removeBankAccountOptions($resolver);
            }

            if ($profileType === ProfileType::ECHECK_TYPE) {
                $this->addBankAccountOptions($resolver);
                $this->removeCreditCardOptions($resolver);
            }
        }
    }

    /**
     * @param OptionsResolver $resolver
     */
    private function addCreditCardOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(CardNumber::CARD_NUMBER);
        $resolver->setRequired(ExpirationDate::EXPIRATION_DATE);
    }

    /**
     * @param OptionsResolver $resolver
     */
    private function addBankAccountOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(AccountNumber::ACCOUNT_NUMBER);
        $resolver->setRequired(RoutingNumber::ROUTING_NUMBER);
        $resolver->setRequired(NameOnAccount::NAME_ON_ACCOUNT);
        $resolver->setRequired(AccountType::ACCOUNT_TYPE);
        $resolver->setRequired(BankName::BANK_NAME);
    }

    /**
     * @param OptionsResolver $resolver
     */
    private function addEncodedDataOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(DataDescriptor::DATA_DESCRIPTOR);
        $resolver->setRequired(DataValue::DATA_VALUE);
    }

    /**
     * @param OptionsResolver $resolver
     */
    private function removeCreditCardOptions(OptionsResolver $resolver)
    {
        $resolver->remove(CardNumber::CARD_NUMBER);
        $resolver->remove(ExpirationDate::EXPIRATION_DATE);
    }

    /**
     * @param OptionsResolver $resolver
     */
    private function removeBankAccountOptions(OptionsResolver $resolver)
    {
        $resolver->remove(AccountNumber::ACCOUNT_NUMBER);
        $resolver->remove(RoutingNumber::ROUTING_NUMBER);
        $resolver->remove(NameOnAccount::NAME_ON_ACCOUNT);
        $resolver->remove(AccountType::ACCOUNT_TYPE);
        $resolver->remove(BankName::BANK_NAME);
    }

    /**
     * @param OptionsResolver $resolver
     */
    private function removeEncodedDataOptions(OptionsResolver $resolver)
    {
        $resolver->remove(DataDescriptor::DATA_DESCRIPTOR);
        $resolver->remove(DataValue::DATA_VALUE);
    }
}
