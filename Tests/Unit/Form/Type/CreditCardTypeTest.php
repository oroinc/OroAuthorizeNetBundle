<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\Form\Type;

use Oro\Bundle\AuthorizeNetBundle\Form\Type\CreditCardExpirationDateType;
use Oro\Bundle\AuthorizeNetBundle\Form\Type\CreditCardType;
use Oro\Component\Testing\Unit\PreloadedExtension;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Test\FormIntegrationTestCase;
use Symfony\Component\Validator\Validation;

class CreditCardTypeTest extends FormIntegrationTestCase
{
    /**
     * @var CreditCardType
     */
    protected $formType;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->formType = new CreditCardType();
        parent::setUp();
    }

    /**
     * @return array
     */
    protected function getExtensions()
    {
        return [
            new PreloadedExtension(
                [
                    $this->formType,
                    CreditCardExpirationDateType::class => new CreditCardExpirationDateType(),
                ],
                []
            ),
            new ValidatorExtension(Validation::createValidator()),
        ];
    }

    public function testConfigureOptions()
    {
        $form = $this->factory->create(CreditCardType::class);
        $this->assertEquals('oro.authorize_net.methods.credit_card.label', $form->getConfig()->getOption('label'));
    }
}
