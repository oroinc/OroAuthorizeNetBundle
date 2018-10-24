<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option\Address;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option\AbstractOption;

/**
 * Option class to represent state field (Authorize.Net SDK, Customer Profile)
 */
class State extends AbstractOption
{
    const STATE = 'state';

    /**
     * {@inheritdoc}
     */
    protected function getName()
    {
        return self::STATE;
    }

    /**
     * {@inheritdoc}
     */
    protected function getAllowedTypes()
    {
        return 'string';
    }
}
