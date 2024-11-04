<?php

namespace Oro\Bundle\AuthorizeNetBundle\Integration;

use Oro\Bundle\IntegrationBundle\Provider\ChannelInterface;
use Oro\Bundle\IntegrationBundle\Provider\IconAwareIntegrationInterface;

/**
 * Authorize.Net integration channel type
 */
class AuthorizeNetChannelType implements ChannelInterface, IconAwareIntegrationInterface
{
    const TYPE = 'authorize_net';

    #[\Override]
    public function getLabel()
    {
        return 'oro.authorize_net.channel_type.authorize_net.label';
    }

    #[\Override]
    public function getIcon()
    {
        return 'bundles/oroauthorizenet/img/authorize-net-logo.png';
    }
}
