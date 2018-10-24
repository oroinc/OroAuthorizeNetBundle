<?php

namespace Oro\Bundle\AuthorizeNetBundle\Method\View;

use Oro\Bundle\AuthorizeNetBundle\Form\Type\CheckoutCredicardProfileType;
use Oro\Bundle\AuthorizeNetBundle\Form\Type\CreditCardType;
use Oro\Bundle\PaymentBundle\Context\PaymentContextInterface;

/**
 * Payment methos view
 */
class AuthorizeNetPaymentMethodView extends AbstractAuthorizeNetPaymentMethodView
{
    /**
     * {@inheritDoc}
     */
    public function getOptions(PaymentContextInterface $context)
    {
        $allowedCreditCards = $this->config->getAllowedCreditCards();

        $formOptions = [
            'requireCvvEntryEnabled' => $this->config->isRequireCvvEntryEnabled(),
            'allowedCreditCards' => $allowedCreditCards
        ];

        if ($this->isCIMEnabled()) {
            $formClass = CheckoutCredicardProfileType::class;
        } else {
            $formClass = CreditCardType::class;
        }

        $form = $this->formFactory->create($formClass, null, $formOptions);

        return [
            'formView' => $form->createView(),
            'paymentMethodComponentOptions' => [
                'allowedCreditCards' => $allowedCreditCards,
                'clientKey' => $this->config->getClientKey(),
                'apiLoginID' => $this->config->getApiLoginId(),
                'testMode' => $this->config->isTestMode()
            ]
        ];
    }
}
