<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Behat\Mock\Method\View;

use Oro\Bundle\AuthorizeNetBundle\Method\View\AuthorizeNetPaymentMethodView;
use Oro\Bundle\PaymentBundle\Context\PaymentContextInterface;

class AuthorizeNetPaymentMethodViewMock extends AuthorizeNetPaymentMethodView
{
    public function getOptions(PaymentContextInterface $context)
    {
        $options = parent::getOptions($context);
        $options['paymentMethodComponentOptions']['acceptJsUrls'] = [
            'test' => 'oroauthorizenet/js/stubs/AcceptStub',
            'prod' => 'oroauthorizenet/js/stubs/AcceptStub',
        ];
        return $options;
    }
}
