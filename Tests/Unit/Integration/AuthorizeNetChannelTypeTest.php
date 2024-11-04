<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\Integration;

use Oro\Bundle\AuthorizeNetBundle\Integration\AuthorizeNetChannelType;

class AuthorizeNetChannelTypeTest extends \PHPUnit\Framework\TestCase
{
    private AuthorizeNetChannelType $channel;

    #[\Override]
    protected function setUp(): void
    {
        $this->channel = new AuthorizeNetChannelType();
    }

    public function testGetLabelReturnsString()
    {
        $this->assertIsString($this->channel->getLabel());
    }

    public function testGetIconReturnsString()
    {
        $this->assertIsString($this->channel->getIcon());
    }
}
