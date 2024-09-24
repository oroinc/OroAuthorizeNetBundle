<?php

namespace Oro\Bundle\AuthorizeNetBundle\Method\Config\Factory;

use Oro\Bundle\AuthorizeNetBundle\Entity\AuthorizeNetSettings;
use Oro\Bundle\AuthorizeNetBundle\Method\Config\AuthorizeNetConfig;
use Oro\Bundle\PaymentBundle\Method\PaymentMethodInterface;

/**
 * Creates instance of AuthorizeNetConfigInterface
 * from AuthorizeNetSettings for eCheck payment method
 */
class AuthorizeNetEcheckConfigFactory extends AuthorizeNetConfigFactory
{
    /**
     * @param AuthorizeNetSettings $settings
     *
     * @return AuthorizeNetConfig
     */
    #[\Override]
    public function createConfig(AuthorizeNetSettings $settings)
    {
        $config = parent::createConfig($settings);

        $label = $this->getLocalizedValue($settings->getECheckLabels());
        $shortLabel = $this->getLocalizedValue($settings->getECheckShortLabels());

        // only CHARGE action is allowed for eCheck
        $config->set(AuthorizeNetConfig::PURCHASE_ACTION_KEY, PaymentMethodInterface::CHARGE);
        $config->set(AuthorizeNetConfig::FIELD_ADMIN_LABEL, $label);
        $config->set(AuthorizeNetConfig::FIELD_LABEL, $label);
        $config->set(AuthorizeNetConfig::FIELD_SHORT_LABEL, $shortLabel);

        return $config;
    }
}
