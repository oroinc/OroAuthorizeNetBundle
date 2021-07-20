<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Option;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

abstract class AbstractOptionTest extends \PHPUnit\Framework\TestCase
{
    /** @var Option\OptionInterface[] */
    protected $options;

    /** @return array */
    abstract public function configureOptionDataProvider();

    /** @return Option\OptionInterface[] */
    abstract protected function getOptions();

    /**
     * @dataProvider configureOptionDataProvider
     */
    public function testConfigureOption(
        array $options = [],
        array $expectedResult = [],
        array $exceptionAndMessage = []
    ) {
        if ($exceptionAndMessage) {
            list($exception, $message) = $exceptionAndMessage;
            $this->expectException($exception);
            $this->expectExceptionMessage($message);
        }

        $resolver = new Option\OptionsResolver();
        foreach ($this->options as $option) {
            $resolver->addOption($option);
        }
        $resolvedOptions = $resolver->resolve($options);

        if ($expectedResult) {
            // Sort array to avoid different order in strict comparison
            sort($expectedResult);
            sort($resolvedOptions);
            $this->assertSame($expectedResult, $resolvedOptions);
        } else {
            $this->assertEmpty($resolvedOptions);
        }
    }

    protected function setUp(): void
    {
        $this->options = $this->getOptions();
    }

    protected function tearDown(): void
    {
        unset($this->options);
    }
}
