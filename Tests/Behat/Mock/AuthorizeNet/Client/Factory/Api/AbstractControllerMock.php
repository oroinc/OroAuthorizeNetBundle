<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Behat\Mock\AuthorizeNet\Client\Factory\Api;

use net\authorize\api\constants\ANetEnvironment;
use net\authorize\api\controller\base\ApiOperationBase;

abstract class AbstractControllerMock extends ApiOperationBase
{
    #[\Override]
    public function getApiResponse()
    {
        throw new \RuntimeException('This method must not be called in tests');
    }

    #[\Override]
    public function execute($endPoint = ANetEnvironment::CUSTOM)
    {
        throw new \RuntimeException('This method must not be called in tests');
    }
}
