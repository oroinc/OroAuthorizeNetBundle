<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Client\RequestConfigurator;

use net\authorize\api\contract\v1 as AnetAPI;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * Default (fallback) Request Configurator
 */
class FallbackRequestConfigurator implements RequestConfiguratorInterface
{
    /**
     * @var PropertyAccessor
     */
    private $propertyAccessor;

    public function __construct(PropertyAccessor $propertyAccessor)
    {
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * {@inheritdoc}
     */
    public function isApplicable(AnetAPI\ANetApiRequestType $request, array $options)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(AnetAPI\ANetApiRequestType $request, array &$options)
    {
        foreach ($options as $key => $value) {
            $this->propertyAccessor->setValue($request, $key, $value);
        }
    }
}
