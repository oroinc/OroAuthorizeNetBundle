<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\Form\Type;

use Oro\Bundle\AuthorizeNetBundle\Form\Type\CreditCardCvvType;
use Oro\Bundle\AuthorizeNetBundle\Form\Type\CreditCardExpirationDateType;
use Oro\Bundle\AuthorizeNetBundle\Form\Type\CreditCardType;
use Oro\Component\Testing\Unit\PreloadedExtension;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Test\FormIntegrationTestCase;
use Symfony\Component\Validator\Validation;
use Symfony\Contracts\Translation\TranslatorInterface;

class CreditCardTypeTest extends FormIntegrationTestCase
{
    /** @var TranslatorInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $translator;

    /** @var CreditCardType */
    private $formType;

    protected function setUp(): void
    {
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->formType = new CreditCardType();
        parent::setUp();
    }

    /**
     * {@inheritDoc}
     */
    protected function getExtensions()
    {
        return [
            new PreloadedExtension(
                [
                    $this->formType,
                    CreditCardExpirationDateType::class => new CreditCardExpirationDateType(),
                    CreditCardCvvType::class => new CreditCardCvvType($this->translator)
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
