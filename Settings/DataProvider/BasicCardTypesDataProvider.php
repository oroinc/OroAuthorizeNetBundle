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
    public const VISA = 'visa';

    /**
     * @internal
     */
    public const MASTERCARD = 'mastercard';

    /**
     * @internal
     */
    public const DISCOVER = 'discover';

    /**
     * @internal
     */
    public const AMERICAN_EXPRESS = 'american_express';

    /**
     * @internal
     */
    public const JCB = 'jcb';

    /**
     * @internal
     */
    public const DINERS_CLUB = 'diners_club';

    /**
     * @internal
     */
    public const CHINA_UNION_PAY = 'china_union_pay';

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
