<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\Form\Type;

use Oro\Bundle\AuthorizeNetBundle\Form\Type\CreditCardExpirationDateType;
use Oro\Component\Testing\Unit\PreloadedExtension;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormConfigInterface;
use Symfony\Component\Form\Test\FormIntegrationTestCase;

class CreditCardExpirationDateTypeTest extends FormIntegrationTestCase
{
    private const YEAR_PERIOD = 10;

    private CreditCardExpirationDateType $formType;

    #[\Override]
    protected function setUp(): void
    {
        $this->formType = new CreditCardExpirationDateType();
        parent::setUp();
    }

    #[\Override]
    protected function getExtensions(): array
    {
        return [
            new PreloadedExtension([$this->formType], [])
        ];
    }

    public function formConfigurationProvider(): array
    {
        return [
            [
                [
                    'month' => [
                        'type' => ChoiceType::class,
                        'options' => [
                            'required' => true,
                        ],
                    ],
                    'year' => [
                        'type' => ChoiceType::class,
                        'options' => [
                            'required' => true,
                        ],
                    ],
                ],
                [
                    'model_timezone' => 'UTC',
                    'view_timezone' => 'UTC',
                    'format' => 'dMy',
                    'input' => 'array',
                    'years' => range(date('y'), date('y') + self::YEAR_PERIOD),
                    'months' => ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12']
                ]
            ],
        ];
    }

    /**
     * @dataProvider formConfigurationProvider
     */
    public function testFormConfiguration(array $formFields, array $formOptions)
    {
        $form = $this->factory->create(CreditCardExpirationDateType::class);
        $this->assertFormOptions($form->getConfig(), $formOptions);
        foreach ($formFields as $fieldname => $fieldData) {
            $this->assertTrue($form->has($fieldname));
            $field = $form->get($fieldname);
            $this->assertInstanceOf($fieldData['type'], $field->getConfig()->getType()->getInnerType());
            $this->assertFormOptions($field->getConfig(), $fieldData['options']);
        }
    }

    private function assertFormOptions(FormConfigInterface $formConfig, array $formOptions): void
    {
        $options = $formConfig->getOptions();
        foreach ($formOptions as $formOptionName => $formOptionData) {
            $this->assertTrue($formConfig->hasOption($formOptionName));
            $this->assertEquals($formOptionData, $options[$formOptionName]);
        }
    }
}
