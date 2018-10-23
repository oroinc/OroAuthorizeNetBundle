<?php

namespace Oro\Bundle\AuthorizeNetBundle\Settings\DataProvider;

/**
 * Credit card type provider
 */
interface CardTypesDataProviderInterface
{
    /**
     * @return string[]
     */
    public function getCardTypes();
}
