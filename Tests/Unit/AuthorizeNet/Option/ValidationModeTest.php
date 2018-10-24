<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Option;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;

class ValidationModeTest extends AbstractOptionTest
{
    /** {@inheritdoc} */
    protected function getOptions()
    {
        return [new Option\ValidationMode()];
    }

    /** {@inheritdoc} */
    public function configureOptionDataProvider()
    {
        return [
            'required' => [
                [],
                [],
                [
                    'Symfony\Component\OptionsResolver\Exception\MissingOptionsException',
                    'The required option "validation_mode" is missing.',
                ],
            ],
            'invalid_value' => [
                ['validation_mode' => 1],
                [],
                [
                    'Symfony\Component\OptionsResolver\Exception\InvalidOptionsException',
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
     * @param string $allowedValue
     */
    public function testAllowedValues($allowedValue)
    {
        $validationMode = new Option\ValidationMode();
        $resolver = new Option\OptionsResolver();

        $resolver->addOption($validationMode);
        $resolved = $resolver->resolve(['validation_mode' => $allowedValue]);
        $this->assertArrayHasKey('validation_mode', $resolved);
        $this->assertEquals($allowedValue, $resolved['validation_mode']);
    }

    public function allowedValuesDataProvider()
    {
        return [
            ['testMode'],
            ['liveMode']
        ];
    }
}
