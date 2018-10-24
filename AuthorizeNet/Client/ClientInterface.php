<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Client;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Response\ResponseInterface;

/**
 * AuthorizeNet API Client interface
 */
interface ClientInterface
{
    /**
     * @param string $hostAddress
     * @param string $requestType
     * @param array $options
     * @return ResponseInterface
     */
    public function send(string $hostAddress, string $requestType, array $options = []);
}
