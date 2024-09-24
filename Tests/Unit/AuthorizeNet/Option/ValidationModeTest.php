<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Option;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

class ValidationModeTest extends AbstractOptionTest
{
    #[\Override]
    protected function getOptions(): array
    {
        return [new Option\ValidationMode()];
    }

    #[\Override]
    public function configureOptionDataProvider(): array
    {
        return [
            'required' => [
                [],
                [],
                [
                    MissingOptionsException::class,
                    'The required option "validation_mode" is missing.',
                ],
            ],
            'invalid_value' => [
                ['validation_mode' => 1],
                [],
                [
                    InvalidOptionsException::class,
                    'The option "validation_mode" with value 1 is invalid. Accepted values are: '.
                    '"testMode", "liveMode"',
                ],
            ],
            'valid' => [
                ['validation_mode' => 'liveMode'],
                ['validation_mode' => 'liveMode'],
            ]

        ];
    }

    /**
     * @dataProvider allowedValuesDataProvider
     */
    public function testAllowedValues(string $allowedValue)
    {
        $validationMode = new Option\ValidationMode();
        $resolver = new Option\OptionsResolver();

        $resolver->addOption($validationMode);
        $resolved = $resolver->resolve(['validation_mode' => $allowedValue]);
        $this->assertArrayHasKey('validation_mode', $resolved);
        $this->assertEquals($allowedValue, $resolved['validation_mode']);
    }

    public function allowedValuesDataProvider(): array
    {
        return [
            ['testMode'],
            ['liveMode']
        ];
    }
}
