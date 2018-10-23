<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Client\RequestConfigurator;

use net\authorize\api\contract\v1 as AnetAPI;

/**
 * Request Configurator interface
 */
interface RequestConfiguratorInterface
{
    /**
     * @return int
     */
    public function getPriority();

    /**
     * @param AnetAPI\ANetApiRequestType $request
     * @param array $options
     * @return bool
     */
    public function isApplicable(AnetAPI\ANetApiRequestType $request, array $options);

    /**
     * @param AnetAPI\ANetApiRequestType $request
     * @param array $options
     */
    public function handle(AnetAPI\ANetApiRequestType $request, array &$options);
}
