<?php

namespace Oro\Bundle\AuthorizeNetBundle\Settings\DataProvider;

/**
 * Available credit card types provider
 */
class BasicCardTypesDataProvider implements CardTypesDataProviderInterface
{
    /**
     * @internal
     */
    const VISA = 'visa';

    /**
     * @internal
     */
    const MASTERCARD = 'mastercard';

    /**
     * @internal
     */
    const DISCOVER = 'discover';

    /**
     * @internal
     */
    const AMERICAN_EXPRESS = 'american_express';

    /**
     * @internal
     */
    const JCB = 'jcb';

    /**
     * @internal
     */
    const DINERS_CLUB = 'diners_club';

    /**
     * @internal
     */
    const CHINA_UNION_PAY = 'china_union_pay';

    /**
     * @return string[]
     */
    #[\Override]
    public function getCardTypes()
    {
        return [
            self::VISA,
            self::MASTERCARD,
            self::DISCOVER,
            self::AMERICAN_EXPRESS,
            self::JCB,
            self::DINERS_CLUB,
            self::CHINA_UNION_PAY,
        ];
    }
}
