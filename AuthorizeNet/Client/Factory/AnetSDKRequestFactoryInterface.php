<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Client\Factory;

use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;

/**
 * Factory interface for creating appropriate API request class by request type
 * & creating API controller by API request
 */
interface AnetSDKRequestFactoryInterface
{
    /**
     * @param string $type
     * @param array $options
     * @return AnetAPI\ANetApiRequestType
     */
    public function createRequest(string $type, array $options = []);

    /**
     * @param AnetAPI\ANetApiRequestType $request
     * @return AnetController\base\IApiOperation
     */
    public function createController(AnetAPI\ANetApiRequestType $request);
}
