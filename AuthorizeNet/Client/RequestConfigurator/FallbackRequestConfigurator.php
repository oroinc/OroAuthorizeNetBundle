<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Client\RequestConfigurator;

use net\authorize\api\contract\v1 as AnetAPI;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Default (fallback) Request Configurator
 */
class FallbackRequestConfigurator implements RequestConfiguratorInterface
{
    private PropertyAccessorInterface $propertyAccessor;

    public function __construct(PropertyAccessorInterface $propertyAccessor)
    {
        $this->propertyAccessor = $propertyAccessor;
    }

    #[\Override]
    public function isApplicable(AnetAPI\ANetApiRequestType $request, array $options)
    {
        return true;
    }

    #[\Override]
    public function handle(AnetAPI\ANetApiRequestType $request, array &$options)
    {
        foreach ($options as $key => $value) {
            $this->propertyAccessor->setValue($request, $key, $value);
        }
    }
}
