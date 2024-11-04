<?php

namespace Oro\Bundle\AuthorizeNetBundle\Method\Config\Provider;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\AuthorizeNetBundle\Entity\AuthorizeNetSettings;
use Oro\Bundle\AuthorizeNetBundle\Integration\AuthorizeNetChannelType;
use Oro\Bundle\AuthorizeNetBundle\Method\Config\AuthorizeNetConfigInterface;
use Oro\Bundle\AuthorizeNetBundle\Method\Config\Factory\AuthorizeNetConfigFactoryInterface;
use Psr\Log\LoggerInterface;

/**
 * Config provider of Authorize.Net payment method
 */
class AuthorizeNetConfigProvider implements AuthorizeNetConfigProviderInterface
{
    /**
     * @var AuthorizeNetConfigInterface[]|null
     */
    protected $configs;

    /**
     * @var ManagerRegistry
     */
    protected $doctrine;

    /**
     * @var AuthorizeNetConfigFactoryInterface
     */
    protected $configFactory;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(
        ManagerRegistry $doctrine,
        LoggerInterface $logger,
        AuthorizeNetConfigFactoryInterface $configFactory
    ) {
        $this->doctrine = $doctrine;
        $this->logger = $logger;
        $this->configFactory = $configFactory;
    }

    /**
     * @param string $identifier
     *
     * @return bool
     */
    #[\Override]
    public function hasPaymentConfig($identifier)
    {
        $configs = $this->getPaymentConfigs();

        return array_key_exists($identifier, $configs);
    }

    #[\Override]
    public function getPaymentConfigs()
    {
        if ($this->configs === null) {
            $this->configs = $this->collectConfigs();
        }

        return $this->configs;
    }

    #[\Override]
    public function getPaymentConfig($identifier)
    {
        if (!$this->hasPaymentConfig($identifier)) {
            return null;
        }

        $configs = $this->getPaymentConfigs();

        return $configs[$identifier];
    }

    /**
     * @return AuthorizeNetSettings[]
     */
    protected function getEnabledIntegrationSettings()
    {
        try {
            $settings = $this->doctrine->getManagerForClass(AuthorizeNetSettings::class)
                ->getRepository(AuthorizeNetSettings::class)
                ->getEnabledSettingsByType(AuthorizeNetChannelType::TYPE);
        } catch (\UnexpectedValueException $e) {
            $this->logger->critical($e->getMessage());
            $settings = [];
        }

        return $settings;
    }

    /**
     * @return AuthorizeNetSettings[]
     */
    protected function getSettings()
    {
        return $this->getEnabledIntegrationSettings();
    }

    /**
     * @return array
     */
    protected function collectConfigs()
    {
        $configs = [];
        $settings = $this->getSettings();

        foreach ($settings as $setting) {
            $config = $this->configFactory->createConfig($setting);
            $configs[$config->getPaymentMethodIdentifier()] = $config;
        }

        return $configs;
    }
}
