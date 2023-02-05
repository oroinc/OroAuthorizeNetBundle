<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\Form\Type;

use Oro\Bundle\AuthorizeNetBundle\Form\Type\PaymentProfileEncodedDataType;
use Oro\Bundle\AuthorizeNetBundle\Model\DTO\PaymentProfileEncodedDataDTO;
use Oro\Component\Testing\Unit\FormIntegrationTestCase;
use Oro\Component\Testing\Unit\PreloadedExtension;

class PaymentProfileEncodedDataTypeTest extends FormIntegrationTestCase
{
    private PaymentProfileEncodedDataType $formType;

    protected function setUp(): void
    {
        $this->formType = new PaymentProfileEncodedDataType();
        parent::setUp();
    }

    /**
     * {@inheritDoc}
     */
    protected function getExtensions(): array
    {
        return array_merge(parent::getExtensions(), [
            new PreloadedExtension([$this->formType], [])
        ]);
    }

    /**
     * @dataProvider submitProvider
     */
    public function testSubmit(
        array $submittedData,
        mixed $expectedData,
        mixed $defaultData = null,
        array $options = [],
        bool $isValid = true
    ) {
        $form = $this->factory->create(PaymentProfileEncodedDataType::class, $defaultData, $options);

        $this->assertEquals($defaultData, $form->getData());

        $form->submit($submittedData);
        $this->assertEquals($isValid, $form->isValid());
        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($expectedData, $form->getData());
    }

    public function submitProvider(): array
    {
        $filledDTO = new PaymentProfileEncodedDataDTO();
        $filledDTO->setDescriptor('encoded descriptor');
        $filledDTO->setValue('encoded value');

        return [
            'empty data valid' => [
                'submittedData' => [],
                'expectedData' => new PaymentProfileEncodedDataDTO(),
                'defaultData' => null,
                'options' => [],
                'isValid' => true
            ],
            'full data valid' => [
                'submittedData' => [
                    'descriptor' => 'encoded descriptor',
                    'value' => 'encoded value',
                ],
                'expectedData' => $filledDTO,
                'defaultData' => null,
                'options' => [],
                'isValid' => true
            ]
        ];
    }
}
