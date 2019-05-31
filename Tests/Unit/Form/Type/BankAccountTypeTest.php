<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\Form\Type;

use Oro\Bundle\AuthorizeNetBundle\Form\Type\BankAccountType;
use Oro\Component\Testing\Unit\FormIntegrationTestCase;
use Oro\Component\Testing\Unit\PreloadedExtension;
use Symfony\Component\Translation\TranslatorInterface;

class BankAccountTypeTest extends FormIntegrationTestCase
{
    /** @var BankAccountType */
    protected $formType;

    /** @var TranslatorInterface */
    private $translator;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->formType = new BankAccountType($this->translator);
        parent::setUp();
    }

    /**
     * @return array
     */
    protected function getExtensions()
    {
        return array_merge(parent::getExtensions(), [
            new PreloadedExtension(
                [
                    BankAccountType::class => $this->formType
                ],
                []
            ),
            $this->getValidatorExtension(true)
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
        $form = $this->factory->create(BankAccountType::class, $defaultData, $options);

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
