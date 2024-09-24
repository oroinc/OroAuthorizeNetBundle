<?php

namespace Oro\Bundle\AuthorizeNetBundle\Method\View\Provider;

use Oro\Bundle\AuthorizeNetBundle\Method\Config\AuthorizeNetConfigInterface;
use Oro\Bundle\AuthorizeNetBundle\Method\Config\Provider\AuthorizeNetConfigProviderInterface;
use Oro\Bundle\AuthorizeNetBundle\Method\View\Factory\AuthorizeNetPaymentMethodViewFactoryInterface;
use Oro\Bundle\PaymentBundle\Method\View\AbstractPaymentMethodViewProvider;

/**
 * Authorize.Net payment method view provider factory
 */
class AuthorizeNetMethodViewProvider extends AbstractPaymentMethodViewProvider
{
    /** @var AuthorizeNetPaymentMethodViewFactoryInterface*/
    private $paymentMethodFactory;

    /** @var AuthorizeNetConfigProviderInterface */
    private $configProvider;

    public function __construct(
        AuthorizeNetPaymentMethodViewFactoryInterface $paymentMethodFactory,
        AuthorizeNetConfigProviderInterface $configProvider
    ) {
        $this->paymentMethodFactory = $paymentMethodFactory;
        $this->configProvider = $configProvider;

        parent::__construct();
    }

    #[\Override]
    protected function buildViews()
    {
        $configs = $this->configProvider->getPaymentConfigs();
        foreach ($configs as $config) {
            $this->addPaymentMethodView($config);
        }
    }

    protected function addPaymentMethodView(AuthorizeNetConfigInterface $config)
    {
        $this->addView(
            $config->getPaymentMethodIdentifier(),
            $this->paymentMethodFactory->create($config)
        );
    }
}
