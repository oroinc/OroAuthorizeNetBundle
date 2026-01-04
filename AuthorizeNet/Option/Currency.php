<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

/**
 * Option class to represent currency field (Authorize.Net SDK, CreateTransactionRequest)
 */
class Currency extends AbstractOption
{
    public const CURRENCY = 'currency';

    public const AUSTRALIAN_DOLLAR = 'AUD';
    public const US_DOLLAR = 'USD';
    public const CANADIAN_DOLLAR = 'CAD';
    public const EURO = 'EUR';
    public const BRITISH_POUND = 'GBP';
    public const NEW_ZEALAND_DOLLAR = 'NZD';

    public const ALL_CURRENCIES = [
        Currency::AUSTRALIAN_DOLLAR,
        Currency::US_DOLLAR,
        Currency::CANADIAN_DOLLAR,
        Currency::EURO,
        Currency::BRITISH_POUND,
        Currency::NEW_ZEALAND_DOLLAR,
    ];

    #[\Override]
    protected function getName()
    {
        return self::CURRENCY;
    }

    #[\Override]
    protected function getAllowedValues()
    {
        return self::ALL_CURRENCIES;
    }
}
