<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Request;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Request\AbstractRequest;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Request\RequestInterface;

abstract class AbstractRequestTest extends \PHPUnit\Framework\TestCase
{
    const DEFAULT_REQUEST_OPTIONS = [
        Option\ApiLoginId::API_LOGIN_ID => 'some_login_id',
        Option\TransactionKey::TRANSACTION_KEY => 'some_transaction_key',
    ];

    /**
     * @var AbstractRequest
     */
    protected $request;

    /**
     * @return AbstractRequest
     */
    protected function getRequest()
    {
        return $this->request;
    }

    /**
     * @return array
     */
    abstract public function optionsProvider();

    /**
     * @dataProvider optionsProvider
     */
    public function testConfigureOptions(array $options = [])
    {
        $resolver = new Option\OptionsResolver();

        $request = $this->getRequest();
        $request->configureOptions($resolver);

        $actualOptions = array_merge(
            static::DEFAULT_REQUEST_OPTIONS,
            $options
        );

        self::assertEquals($actualOptions, $resolver->resolve($actualOptions));
    }

    public function getNotRequiredOptionsProvider(): array
    {
        return [];
    }

    public function testGetType()
    {
        $request = $this->getRequest();
        $this->assertInstanceOf(RequestInterface::class, $request);
        $reflection = new \ReflectionClass(\get_class($this->getRequest()));
        if (false !== $staticType = $reflection->getConstant('REQUEST_TYPE')) {
            $this->assertEquals($staticType, $this->getRequest()->getType());
        }
    }
}
