<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

/**
 * Option class to represent shipTo (NameAndAddressType) field (all optional!) (Authorize.Net SDK, Payment Transactions)
 */
class ShippingAddress implements OptionInterface
{
    const PREFIX = 'ship_to_';

    const FIRST_NAME = self::PREFIX . 'first_name';
    const LAST_NAME = self::PREFIX . 'last_name';
    const COMPANY = self::PREFIX . 'company';
    const ADDRESS = self::PREFIX . 'address';
    const CITY = self::PREFIX . 'city';
    const STATE = self::PREFIX . 'state';
    const ZIP = self::PREFIX . 'zip';
    const COUNTRY = self::PREFIX . 'country';

    const ALL_OPTION_KEYS = [
        self::FIRST_NAME,
        self::LAST_NAME,
        self::COMPANY,
        self::ADDRESS,
        self::CITY,
        self::STATE,
        self::ZIP,
        self::COUNTRY
    ];

    #[\Override]
    public function configureOption(OptionsResolver $resolver)
    {
        $allOptionKeys = self::ALL_OPTION_KEYS;
        $resolver->setDefined($allOptionKeys);

        foreach ($allOptionKeys as $optionKey) {
            $resolver->setAllowedTypes($optionKey, 'string');
        }
    }
}
