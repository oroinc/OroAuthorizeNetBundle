<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

/**
 * Option class to represent validationMode field (Authorize.Net SDK)
 */
class ValidationMode extends AbstractOption
{
    public const VALIDATION_MODE = 'validation_mode';

    public const TEST_MODE = 'testMode';
    public const LIVE_MODE = 'liveMode';

    #[\Override]
    protected function getName()
    {
        return self::VALIDATION_MODE;
    }

    #[\Override]
    protected function getAllowedValues()
    {
        return [self::TEST_MODE, self::LIVE_MODE];
    }
}
