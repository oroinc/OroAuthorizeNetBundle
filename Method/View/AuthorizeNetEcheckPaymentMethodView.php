<?php

namespace Oro\Bundle\AuthorizeNetBundle\Method\View;

use Oro\Bundle\AuthorizeNetBundle\Form\Type\BankAccountType;
use Oro\Bundle\AuthorizeNetBundle\Form\Type\CheckoutEcheckProfileType;
use Oro\Bundle\PaymentBundle\Context\PaymentContextInterface;

/**
 * eCheck payment method view
 */
class AuthorizeNetEcheckPaymentMethodView extends AbstractAuthorizeNetPaymentMethodView
{
    #[\Override]
    public function getOptions(PaymentContextInterface $context)
    {
        $config = $this->config;
        $formOptions = [
            'allowed_account_types' => $config->getECheckAccountTypes(),
            'confirmation_text' => $config->getECheckConfirmationText()
        ];

        if ($this->isCIMEnabled()) {
            $formClass = CheckoutEcheckProfileType::class;
        } else {
            $formClass = BankAccountType::class;
        }

        $form = $this->formFactory->create($formClass, null, $formOptions);

        return [
            'formView' => $form->createView(),
            'paymentMethodComponentOptions' => [
                'clientKey' => $config->getClientKey(),
                'apiLoginID' => $config->getApiLoginId(),
                'testMode' => $config->isTestMode()
            ]
        ];
    }
}
