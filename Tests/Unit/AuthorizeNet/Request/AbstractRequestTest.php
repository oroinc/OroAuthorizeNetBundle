<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Request;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Request\AbstractRequest;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Request\RequestInterface;

abstract class AbstractRequestTest extends \PHPUnit\Framework\TestCase
{
    private const DEFAULT_REQUEST_OPTIONS = [
        Option\ApiLoginId::API_LOGIN_ID => 'some_login_id',
        Option\TransactionKey::TRANSACTION_KEY => 'some_transaction_key',
    ];

    protected AbstractRequest $request;

    abstract public function optionsProvider(): array;

    /**
     * @dataProvider optionsProvider
     */
    public function testConfigureOptions(array $options = [])
    {
        $resolver = new Option\OptionsResolver();

        $this->request->configureOptions($resolver);

        $actualOptions = array_merge(self::DEFAULT_REQUEST_OPTIONS, $options);

        self::assertEquals($actualOptions, $resolver->resolve($actualOptions));
    }

    public function getNotRequiredOptionsProvider(): array
    {
        return [];
    }

    public function testGetType()
    {
        $this->assertInstanceOf(RequestInterface::class, $this->request);
        $reflection = new \ReflectionClass(\get_class($this->request));
        $staticType = $reflection->getConstant('REQUEST_TYPE');
        if (false !== $staticType) {
            $this->assertEquals($staticType, $this->request->getType());
        }
    }
}
