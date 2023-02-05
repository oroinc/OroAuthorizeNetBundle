<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\Form\Type;

use Oro\Bundle\AuthorizeNetBundle\Form\Type\BankAccountType;
use Oro\Component\Testing\Unit\FormIntegrationTestCase;
use Oro\Component\Testing\Unit\PreloadedExtension;
use Symfony\Contracts\Translation\TranslatorInterface;

class BankAccountTypeTest extends FormIntegrationTestCase
{
    private BankAccountType $formType;

    protected function setUp(): void
    {
        $this->formType = new BankAccountType($this->createMock(TranslatorInterface::class));
        parent::setUp();
    }

    /**
     * {@inheritDoc}
     */
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
        $form = $this->factory->create(BankAccountType::class, $defaultData, $options);

        $this->assertEquals($defaultData, $form->getData());

        $form->submit($submittedData);
        $this->assertEquals($isValid, $form->isValid());
        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($expectedData, $form->getData());
    }

    public function submitProvider(): array
    {
        return [
            'empty data not valid' => [
                'submittedData' => [],
                'expectedData' => [
                    'accountType' => null,
                    'accountNumber' => null,
                    'routingNumber' => null,
                    'nameOnAccount' => null,
                    'bankName' => null
                ],
                'defaultData' => null,
                'options' => [],
                'isValid' => false
            ],
            'full data valid' => [
                'submittedData' => [
                    'accountType' => 'checking',
                    'accountNumber' => '987654321',
                    'routingNumber' => '123456789',
                    'nameOnAccount' => 'John Doe',
                    'bankName' => 'ORO BANK'
                ],
                'expectedData' => [
                    'accountType' => 'checking',
                    'accountNumber' => '987654321',
                    'routingNumber' => '123456789',
                    'nameOnAccount' => 'John Doe',
                    'bankName' => 'ORO BANK'
                ],
                'defaultData' => null,
                'options' => [],
                'isValid' => true
            ]
        ];
    }
}
