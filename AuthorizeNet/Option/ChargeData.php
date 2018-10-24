<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option\Address as AddressOption;

/**
 * Option class that configures required options, depending on chargeType option
 *
 * in case chargeType===TYPE_CREDIT_CARD, required options are:
 *  [DataDescriptor, DataValue]
 * in case TYPE_PAYMENT_PROFILE
 *  [CustomerProfileId, CustomerPaymentProfileId, CardCode|null]
 */
class ChargeData implements OptionInterface, OptionsDependentInterface
{
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOption(OptionsResolver $resolver)
    {
        $resolver
            ->addOption(new ChargeType())
            ->addOption(new CustomerAddress())
            ->addOption(new CustomerDataId($isRequired = false))
            ->addOption(new Email($isRequired = false))
            ->addOption(new CreateProfile($isRequired = false))
            ->addOption(new DataDescriptor($isRequired = false))
            ->addOption(new DataValue($isRequired = false))
            ->addOption(new CustomerProfileId($isRequired = false))
            ->addOption(new CustomerPaymentProfileId($isRequired = false))
            ->addOption(new CardCode($isRequired = false))
            ->addOption(new ShippingAddress())
            ->addOption(new LineItems($isRequired = false))
            ->addOption(new InvoiceNumber($isRequired = false))
            ->addOption(new TaxAmount($isRequired = false))
        ;
    }

    /**
     * Check is applicable this option
     *
     * @param mixed[] $options Options to resolve
     * @return bool
     */
    public function isApplicableDependent(array $options)
    {
        return true;
    }

    /**
     * Configure dependent options
     *
     * @param OptionsResolver $resolver
     * @param mixed[] $options Options to resolve
     */
    public function configureDependentOption(OptionsResolver $resolver, array $options)
    {
        $chargeType = $options[ChargeType::NAME] ?? null;
        $createProfile = $options[CreateProfile::NAME] ?? false;
        switch ($chargeType) {
            case ChargeType::TYPE_CREDIT_CARD:
                $resolver
                    ->setRequired(DataDescriptor::DATA_DESCRIPTOR)
                    ->setRequired(DataValue::DATA_VALUE)
                    ->remove(CustomerPaymentProfileId::CUSTOMER_PAYMENT_PROFILE_ID)
                    ->remove(CardCode::NAME)
                ;

                if (!$createProfile) {
                    $resolver
                        ->remove(CustomerProfileId::CUSTOMER_PROFILE_ID)
                        ->remove(CustomerDataId::NAME)
                        ->remove(Email::EMAIL)
                    ;
                }
                break;
            case ChargeType::TYPE_PAYMENT_PROFILE:
                $resolver
                    ->setRequired(CustomerProfileId::CUSTOMER_PROFILE_ID)
                    ->setRequired(CustomerPaymentProfileId::CUSTOMER_PAYMENT_PROFILE_ID)
                    ->remove(DataDescriptor::DATA_DESCRIPTOR)
                    ->remove(DataValue::DATA_VALUE)
                    ->remove(CustomerDataId::NAME)
                    ->remove(Email::EMAIL)
                    ->remove(AddressOption\FirstName::FIRST_NAME)
                    ->remove(AddressOption\LastName::LAST_NAME)
                    ->remove(AddressOption\Company::COMPANY)
                    ->remove(AddressOption\Address::ADDRESS)
                    ->remove(AddressOption\City::CITY)
                    ->remove(AddressOption\State::STATE)
                    ->remove(AddressOption\Zip::ZIP)
                    ->remove(AddressOption\Country::COUNTRY)
                    ->remove(AddressOption\PhoneNumber::PHONE_NUMBER)
                    ->remove(AddressOption\FaxNumber::FAX_NUMBER)
                ;
                break;
            default:
                break;
        }
    }
}
