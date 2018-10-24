<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

/**
 * Option class to represent validationMode field (Authorize.Net SDK)
 */
class ValidationMode extends AbstractOption
{
    const VALIDATION_MODE = 'validation_mode';

    const TEST_MODE = 'testMode';
    const LIVE_MODE = 'liveMode';

    /**
     * {@inheritdoc}
     */
    protected function getName()
    {
        return self::VALIDATION_MODE;
    }

    /**
     * {@inheritdoc}
     */
    protected function getAllowedValues()
    {
        return [self::TEST_MODE, self::LIVE_MODE];
    }
}
