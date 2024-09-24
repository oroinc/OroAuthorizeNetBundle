<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

/**
 * Class that represents option (Authorize.Net SDK)
 * <customer>
 *   <id>oro-x-xxx</id>
 * </customer>
 *
 * Needed to create profile, if Customer no yet Have Oro CustomerProfile
 */
class CustomerDataId extends AbstractOption
{
    public const NAME = 'customer_data_id';

    /**
     * @return string
     */
    #[\Override]
    protected function getName()
    {
        return self::NAME;
    }

    #[\Override]
    public function getAllowedTypes()
    {
        return 'string';
    }
}
