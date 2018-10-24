<?php

namespace Oro\Bundle\AuthorizeNetBundle\Method\Config\Provider;

use Oro\Bundle\AuthorizeNetBundle\Entity\AuthorizeNetSettings;

/**
 * Config provider of Authorize.Net eCheck payment method
 */
class AuthorizeNetEcheckConfigProvider extends AuthorizeNetConfigProvider
{
    /**
     * @return AuthorizeNetSettings[]
     */
    protected function getSettings()
    {
        $settings = parent::getSettings();
        $settings = array_filter($settings, function (AuthorizeNetSettings $setting) {
            return $setting->isECheckEnabled();
        });

        return $settings;
    }
}
