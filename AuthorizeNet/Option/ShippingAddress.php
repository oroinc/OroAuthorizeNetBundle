<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

/**
 * Option class to represent shipTo (NameAndAddressType) field (all optional!) (Authorize.Net SDK, Payment Transactions)
 */
class ShippingAddress implements OptionInterface
{
    public const PREFIX = 'ship_to_';

    public const FIRST_NAME = self::PREFIX . 'first_name';
    public const LAST_NAME = self::PREFIX . 'last_name';
    public const COMPANY = self::PREFIX . 'company';
    public const ADDRESS = self::PREFIX . 'address';
    public const CITY = self::PREFIX . 'city';
    public const STATE = self::PREFIX . 'state';
    public const ZIP = self::PREFIX . 'zip';
    public const COUNTRY = self::PREFIX . 'country';

    public const ALL_OPTION_KEYS = [
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
