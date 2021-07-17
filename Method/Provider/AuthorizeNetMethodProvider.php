<?php

namespace Oro\Bundle\AuthorizeNetBundle\Method\Provider;

use Oro\Bundle\AuthorizeNetBundle\Method\Config\AuthorizeNetConfigInterface;
use Oro\Bundle\AuthorizeNetBundle\Method\Config\Provider\AuthorizeNetConfigProviderInterface;
use Oro\Bundle\AuthorizeNetBundle\Method\Factory\AuthorizeNetPaymentMethodFactoryInterface;
use Oro\Bundle\PaymentBundle\Method\Provider\AbstractPaymentMethodProvider;

/**
 * Authorize.Net payment method provider
 */
class AuthorizeNetMethodProvider extends AbstractPaymentMethodProvider
{
    /**
     * @var AuthorizeNetPaymentMethodFactoryInterface
     */
    private $paymentMethodFactory;

    /**
     * @var AuthorizeNetConfigProviderInterface
     */
    private $configProvider;

    public function __construct(
        AuthorizeNetConfigProviderInterface $configProvider,
        AuthorizeNetPaymentMethodFactoryInterface $paymentMethodFactory
    ) {
        parent::__construct();
        $this->configProvider = $configProvider;
        $this->paymentMethodFactory = $paymentMethodFactory;
    }

    /**
     * {@inheritdoc}
     */
    protected function collectMethods()
    {
        $configs = $this->configProvider->getPaymentConfigs();
        foreach ($configs as $config) {
            $this->addPaymentMethod($config);
        }
    }

    protected function addPaymentMethod(AuthorizeNetConfigInterface $config)
    {
        $this->addMethod(
            $config->getPaymentMethodIdentifier(),
            $this->paymentMethodFactory->create($config)
        );
    }
}
