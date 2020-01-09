<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\Form\Type;

use Oro\Bundle\AuthorizeNetBundle\Entity\CustomerPaymentProfile;
use Oro\Bundle\AuthorizeNetBundle\Form\Type\PaymentProfileAddressType;
use Oro\Bundle\AuthorizeNetBundle\Form\Type\PaymentProfileType;
use Oro\Bundle\FormBundle\Tests\Unit\Stub\StripTagsExtensionStub;
use Oro\Component\Testing\Unit\FormIntegrationTestCase;
use Oro\Component\Testing\Unit\PreloadedExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;

class PaymentProfileTypeTest extends FormIntegrationTestCase
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
        $this->formType = new PaymentProfileType();
        parent::setUp();
    }

    /**
     * @return array
     */
    protected function getExtensions()
    {
        return array_merge(parent::getExtensions(), [
            new PreloadedExtension([
            ], [
                FormType::class => [
                    new StripTagsExtensionStub($this),
                ],
            ]),
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
        $form = $this->factory->create(PaymentProfileType::class, $defaultData, $options);

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
