<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\Form\Type;

use Oro\Bundle\AuthorizeNetBundle\Form\Type\CreditCardExpirationDateType;
use Oro\Component\Testing\Unit\PreloadedExtension;
use Symfony\Component\Form\FormConfigInterface;
use Symfony\Component\Form\Test\FormIntegrationTestCase;

class CreditCardExpirationDateTypeTest extends FormIntegrationTestCase
{
    const YEAR_PERIOD = 10;

    /**
     * @var CreditCardExpirationDateType
     */
    protected $formType;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->formType = new CreditCardExpirationDateType();
        parent::setUp();
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensions()
    {
        return [
            new PreloadedExtension([$this->formType], [])
        ];
    }

    /**
     * @return array
     */
    public function formConfigurationProvider()
    {
        return [
            [
                [
                    'month' => [
                        'type' => 'choice',
                        'options' => [
                            'required' => true,
                        ],
                    ],
                    'year' => [
                        'type' => 'choice',
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
     * @param array $formFields
     * @param array $formOptions
     */
    public function testFormConfiguration(array $formFields, array $formOptions)
    {
        $form = $this->factory->create(CreditCardExpirationDateType::class);
        $this->assertFormOptions($form->getConfig(), $formOptions);
        foreach ($formFields as $fieldname => $fieldData) {
            $this->assertTrue($form->has($fieldname));
            $field = $form->get($fieldname);
            $this->assertEquals($field->getConfig()->getType()->getName(), $fieldData['type']);
            $this->assertFormOptions($field->getConfig(), $fieldData['options']);
        }
    }

    /**
     * @param FormConfigInterface $formConfig
     * @param array $formOptions
     */
    protected function assertFormOptions(FormConfigInterface $formConfig, array $formOptions)
    {
        $options = $formConfig->getOptions();
        foreach ($formOptions as $formOptionName => $formOptionData) {
            $this->assertTrue($formConfig->hasOption($formOptionName));
            $this->assertEquals($formOptionData, $options[$formOptionName]);
        }
    }
}
