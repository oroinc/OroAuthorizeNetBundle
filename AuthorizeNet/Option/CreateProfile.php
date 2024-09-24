<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

/**
 * Internal control flow option required to figure out if Request should be configured
 * for creating profile (profile Authorize.Net SDK)
 */
class CreateProfile extends AbstractOption
{
    public const NAME = 'create_profile';

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
        return 'bool';
    }
}
