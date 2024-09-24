<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\Form\Type;

use Oro\Bundle\AuthorizeNetBundle\Form\Type\PaymentProfileMaskedDataType;
use Oro\Bundle\AuthorizeNetBundle\Model\DTO\PaymentProfileMaskedDataDTO;
use Oro\Component\Testing\Unit\FormIntegrationTestCase;
use Oro\Component\Testing\Unit\PreloadedExtension;

class PaymentProfileMaskedDataTypeTest extends FormIntegrationTestCase
{
    private PaymentProfileMaskedDataType $formType;

    #[\Override]
    protected function setUp(): void
    {
        $this->formType = new PaymentProfileMaskedDataType();
        parent::setUp();
    }

    #[\Override]
    protected function getExtensions(): array
    {
        return array_merge(parent::getExtensions(), [
            new PreloadedExtension([$this->formType], []),
            $this->getValidatorExtension(true)
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
        $form = $this->factory->create(PaymentProfileMaskedDataType::class, $defaultData, $options);

        $this->assertEquals($defaultData, $form->getData());

        $form->submit($submittedData);
        $this->assertEquals($isValid, $form->isValid());
        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($expectedData, $form->getData());
    }

    public function submitProvider(): array
    {
        $validData = new PaymentProfileMaskedDataDTO();
        $validData->setAccountNumber('XXXX1234');
        $validData->setRoutingNumber('XXXX4321');
        $validData->setNameOnAccount('John Doe');
        $validData->setAccountType('type');
        $validData->setBankName('bank name');

        return [
            'empty data valid' => [
                'submittedData' => [],
                'expectedData' => new PaymentProfileMaskedDataDTO(),
                'defaultData' => null,
                'options' => [],
                'isValid' => true
            ],
            'full data valid' => [
                'submittedData' => [
                    'accountNumber' => 'XXXX1234',
                    'routingNumber' => 'XXXX4321',
                    'nameOnAccount' => 'John Doe',
                    'accountType' => 'type',
                    'bankName' => 'bank name'
                ],
                'expectedData' => $validData,
                'defaultData' => null,
                'options' => [],
                'isValid' => true
            ]
        ];
    }
}
