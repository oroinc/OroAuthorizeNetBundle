<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

/**
 * Option class to represent currency field (Authorize.Net SDK, CreateTransactionRequest)
 */
class Currency extends AbstractOption
{
    const CURRENCY = 'currency';

    const AUSTRALIAN_DOLLAR = 'AUD';
    const US_DOLLAR = 'USD';
    const CANADIAN_DOLLAR = 'CAD';
    const EURO = 'EUR';
    const BRITISH_POUND = 'GBP';
    const NEW_ZEALAND_DOLLAR = 'NZD';

    const ALL_CURRENCIES = [
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
