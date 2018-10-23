<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\Entity;

use Oro\Bundle\AuthorizeNetBundle\Entity\AuthorizeNetSettings;
use Oro\Bundle\LocaleBundle\Entity\LocalizedFallbackValue;
use Oro\Component\Testing\Unit\EntityTestCaseTrait;
use Oro\Component\Testing\Unit\EntityTrait;
use Symfony\Component\HttpFoundation\ParameterBag;

class AuthorizeNetSettingsTest extends \PHPUnit\Framework\TestCase
{
    use EntityTestCaseTrait;
    use EntityTrait;

    public function testAccessors()
    {
        $this->assertPropertyAccessors(
            new AuthorizeNetSettings(),
            [
                ['apiLoginId', 'some string'],
                ['transactionKey', 'some string'],
                ['clientKey', 'some string'],
                ['authNetTestMode', false],
                ['authNetRequireCVVEntry', true],
                ['creditCardPaymentAction', 'charge'],
                ['allowedCreditCardTypes', ['visa']],
                ['eCheckEnabled', true],
                ['eCheckAccountTypes', ['checking']],
                ['eCheckConfirmationText', 'some text']
            ]
        );
        $this->assertPropertyCollections(
            new AuthorizeNetSettings(),
            [
                ['creditCardLabels', new LocalizedFallbackValue()],
                ['creditCardShortLabels', new LocalizedFallbackValue()],
                ['eCheckLabels', new LocalizedFallbackValue()],
                ['eCheckShortLabels', new LocalizedFallbackValue()],
            ]
        );
    }

    public function testGetSettingsBag()
    {
        /** @var AuthorizeNetSettings $entity */
        $entity = $this->getEntity(
            AuthorizeNetSettings::class,
            [
                'apiLoginId' => 'some login',
                'transactionKey' => 'some transaction key',
                'clientKey' => 'some client key',
                'authNetTestMode' => true,
                'authNetRequireCVVEntry' => false,
                'creditCardPaymentAction' => 'charge',
                'allowedCreditCardTypes' => ['visa', 'mastercard'],
                'creditCardLabels' => [(new LocalizedFallbackValue())->setString('label')],
                'creditCardShortLabels' => [(new LocalizedFallbackValue())->setString('lbl')],
                'eCheckEnabled' => true,
                'eCheckLabels' => [(new LocalizedFallbackValue())->setString('echeck label')],
                'eCheckShortLabels' => [(new LocalizedFallbackValue())->setString('echeck short')],
                'eCheckAccountTypes' => ['checking'],
                'eCheckConfirmationText' => 'some text'
            ]
        );

        /** @var ParameterBag $result */
        $result = $entity->getSettingsBag();

        $this->assertEquals('some login', $result->get('api_login_id'));
        $this->assertEquals('some transaction key', $result->get('transaction_key'));
        $this->assertEquals('some client key', $result->get('client_key'));
        $this->assertEquals(true, $result->get('test_mode'));
        $this->assertEquals(false, $result->get('require_cvv_entry'));
        $this->assertEquals($result->get('allowed_credit_card_types'), $entity->getAllowedCreditCardTypes());
        $this->assertEquals($result->get('credit_card_payment_action'), $entity->getCreditCardPaymentAction());
        $this->assertEquals($result->get('credit_card_labels'), $entity->getCreditCardLabels());
        $this->assertEquals($result->get('credit_card_short_labels'), $entity->getCreditCardShortLabels());

        $this->assertEquals(true, $result->get('echeck_enabled'));
        $this->assertEquals($result->get('echeck_labels'), $entity->getECheckLabels());
        $this->assertEquals($result->get('echeck_short_labels'), $entity->getECheckShortLabels());
        $this->assertEquals($result->get('echeck_account_types'), $entity->getECheckAccountTypes());
        $this->assertEquals($result->get('echeck_confirmation_text'), $entity->getECheckConfirmationText());
    }
}
