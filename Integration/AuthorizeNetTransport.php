<?php

namespace Oro\Bundle\AuthorizeNetBundle\Integration;

use Oro\Bundle\AuthorizeNetBundle\Entity\AuthorizeNetSettings;
use Oro\Bundle\AuthorizeNetBundle\Form\Type\AuthorizeNetSettingsType;
use Oro\Bundle\IntegrationBundle\Entity\Transport;
use Oro\Bundle\IntegrationBundle\Provider\TransportInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Authorize.Net integration transport
 */
class AuthorizeNetTransport implements TransportInterface
{
    /** @var ParameterBag */
    protected $settings;

    #[\Override]
    public function init(Transport $transportEntity)
    {
        $this->settings = $transportEntity->getSettingsBag();
    }

    #[\Override]
    public function getSettingsFormType()
    {
        return AuthorizeNetSettingsType::class;
    }

    #[\Override]
    public function getSettingsEntityFQCN()
    {
        return AuthorizeNetSettings::class;
    }

    #[\Override]
    public function getLabel()
    {
        return 'oro.authorize_net.settings.label';
    }
}
