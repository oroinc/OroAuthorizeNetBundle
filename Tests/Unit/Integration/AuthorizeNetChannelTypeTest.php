<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\Integration;

use Oro\Bundle\AuthorizeNetBundle\Integration\AuthorizeNetChannelType;

class AuthorizeNetChannelTypeTest extends \PHPUnit\Framework\TestCase
{
    /** @var AuthorizeNetChannelType */
    private $channel;

    protected function setUp()
    {
        $this->channel = new AuthorizeNetChannelType();
    }

    public function testGetLabelReturnsString()
    {
        $this->assertTrue(is_string($this->channel->getLabel()));
    }

    public function testGetIconReturnsString()
    {
        $this->assertTrue(is_string($this->channel->getIcon()));
    }
}
