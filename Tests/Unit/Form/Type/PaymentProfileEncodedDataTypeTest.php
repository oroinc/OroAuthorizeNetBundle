<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\Form\Type;

use Oro\Bundle\AuthorizeNetBundle\Form\Type\PaymentProfileAddressType;
use Oro\Bundle\AuthorizeNetBundle\Form\Type\PaymentProfileEncodedDataType;
use Oro\Bundle\AuthorizeNetBundle\Model\DTO\PaymentProfileEncodedDataDTO;
use Oro\Component\Testing\Unit\FormIntegrationTestCase;
use Oro\Component\Testing\Unit\PreloadedExtension;

class PaymentProfileEncodedDataTypeTest extends FormIntegrationTestCase
{
    /**
     * @var PaymentProfileAddressType
     */
    protected $formType;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->formType = new PaymentProfileEncodedDataType();
        parent::setUp();
    }

    /**
     * @return array
     */
    protected function getExtensions()
    {
        return array_merge(parent::getExtensions(), [
            new PreloadedExtension([], [])
        ]);
    }

    /**
     * @param array $submittedData
     * @param mixed $expectedData
     * @param mixed $defaultData
     * @param array $options
     * @param bool $isValid
     *
     * @dataProvider submitProvider
     */
    public function testSubmit($submittedData, $expectedData, $defaultData = null, $options = [], $isValid = true)
    {
        $form = $this->factory->create(PaymentProfileEncodedDataType::class, $defaultData, $options);

        $this->assertEquals($defaultData, $form->getData());

        $form->submit($submittedData);
        $this->assertEquals($isValid, $form->isValid());
        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($expectedData, $form->getData());
    }

    /**
     * @return array
     */
    public function submitProvider()
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
