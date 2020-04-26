<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\Integration;

use Oro\Bundle\AuthorizeNetBundle\Entity\AuthorizeNetSettings;
use Oro\Bundle\AuthorizeNetBundle\Form\Type\AuthorizeNetSettingsType;
use Oro\Bundle\AuthorizeNetBundle\Integration\AuthorizeNetTransport;
use Symfony\Component\HttpFoundation\ParameterBag;

class AuthorizeNetTransportTest extends \PHPUnit\Framework\TestCase
{
    /** @var AuthorizeNetTransport */
    private $transport;

    protected function setUp(): void
    {
        $this->transport = new class() extends AuthorizeNetTransport {
            public function xgetSettings(): ParameterBag
            {
                return $this->settings;
            }
        };
    }

    public function testInitCompiles()
    {
        $settings = new AuthorizeNetSettings();
        $this->transport->init($settings);
        static::assertSame($settings->getSettingsBag(), $this->transport->xgetSettings());
    }

    public function testGetSettingsFormType()
    {
        static::assertSame(AuthorizeNetSettingsType::class, $this->transport->getSettingsFormType());
    }

    public function testGetSettingsEntityFQCN()
    {
        static::assertSame(AuthorizeNetSettings::class, $this->transport->getSettingsEntityFQCN());
    }

    public function testGetLabelReturnsString()
    {
        static::assertTrue(is_string($this->transport->getLabel()));
    }
}
