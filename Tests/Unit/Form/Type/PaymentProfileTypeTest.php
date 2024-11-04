<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\Form\Type;

use Oro\Bundle\AuthorizeNetBundle\Entity\CustomerPaymentProfile;
use Oro\Bundle\AuthorizeNetBundle\Form\Type\PaymentProfileType;
use Oro\Bundle\FormBundle\Tests\Unit\Stub\StripTagsExtensionStub;
use Oro\Component\Testing\Unit\FormIntegrationTestCase;
use Oro\Component\Testing\Unit\PreloadedExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;

class PaymentProfileTypeTest extends FormIntegrationTestCase
{
    private PaymentProfileType $formType;

    #[\Override]
    protected function setUp(): void
    {
        $this->formType = new PaymentProfileType();
        parent::setUp();
    }

    #[\Override]
    protected function getExtensions(): array
    {
        return array_merge(parent::getExtensions(), [
            new PreloadedExtension(
                [$this->formType],
                [FormType::class => [new StripTagsExtensionStub($this)]]
            ),
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
        $form = $this->factory->create(PaymentProfileType::class, $defaultData, $options);

        $this->assertEquals($defaultData, $form->getData());

        $form->submit($submittedData);
        $this->assertEquals($isValid, $form->isValid());
        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($expectedData, $form->getData());
    }

    public function submitProvider(): array
    {
        $filledProfile = new CustomerPaymentProfile();
        $filledProfile->setName('name_stripped');
        $filledProfile->setDefault(true);
        $filledProfile->setLastDigits('9999');

        return [
            'empty data not valid' => [
                'submittedData' => [],
                'expectedData' => new CustomerPaymentProfile(),
                'defaultData' => null,
                'options' => [],
                'isValid' => false
            ],
            'full data valid' => [
                'submittedData' => [
                    'name' => 'name',
                    'default' => true,
                    'lastDigits' => '9999'
                ],
                'expectedData' => $filledProfile,
                'defaultData' => null,
                'options' => [],
                'isValid' => true
            ]
        ];
    }
}
