<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Behat\Mock\Twig;

use Oro\Bundle\AssetBundle\Twig\ExternalResourceExtension;

/**
 * Mock External resource twig extension to override js assets.
 */
class ExternalResourceExtensionMock extends ExternalResourceExtension
{
    protected const array RESOURCES_MOCK = [
        'authorize_net_payment_js_test' => 'oroauthorizenet/js/stubs/AcceptStub',
        'authorize_net_payment_js_prod' => 'oroauthorizenet/js/stubs/AcceptStub'
    ];

    public function getExternalResourceLink(string $resourceAlias): string
    {
        if (isset(self::RESOURCES_MOCK[$resourceAlias])) {
            return self::RESOURCES_MOCK[$resourceAlias];
        }

        return parent::getExternalResourceLink($resourceAlias);
    }
}
