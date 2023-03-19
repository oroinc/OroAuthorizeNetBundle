<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\Integration;

use Oro\Bundle\AuthorizeNetBundle\Entity\AuthorizeNetSettings;
use Oro\Bundle\AuthorizeNetBundle\Form\Type\AuthorizeNetSettingsType;
use Oro\Bundle\AuthorizeNetBundle\Integration\AuthorizeNetTransport;
use Oro\Component\Testing\ReflectionUtil;

class AuthorizeNetTransportTest extends \PHPUnit\Framework\TestCase
{
    private AuthorizeNetTransport $transport;

    protected function setUp(): void
    {
        $this->transport = new AuthorizeNetTransport();
    }

    public function testInitCompiles()
    {
        $settings = new AuthorizeNetSettings();

        $this->transport->init($settings);

        self::assertSame(
            $settings->getSettingsBag(),
            ReflectionUtil::getPropertyValue($this->transport, 'settings')
        );
    }

    public function testGetSettingsFormType()
    {
        self::assertSame(AuthorizeNetSettingsType::class, $this->transport->getSettingsFormType());
    }

    public function testGetSettingsEntityFQCN()
    {
        self::assertSame(AuthorizeNetSettings::class, $this->transport->getSettingsEntityFQCN());
    }

    public function testGetLabelReturnsString()
    {
        self::assertIsString($this->transport->getLabel());
    }
}
