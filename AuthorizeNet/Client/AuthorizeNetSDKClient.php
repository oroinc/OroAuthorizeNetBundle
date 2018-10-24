<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Client;

use net\authorize\api\contract\v1\ErrorResponse;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Client\Factory\AnetSDKRequestFactoryInterface;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Response\ResponseFactory;

/**
 * AuthorizeNet API Client to send api request
 */
class AuthorizeNetSDKClient implements ClientInterface
{
    /**
     * @var AnetSDKRequestFactoryInterface
     */
    protected $requestFactory;

    /**
     * @var ResponseFactory
     */
    protected $responseFactory;

    /**
     * @param AnetSDKRequestFactoryInterface $requestFactory
     * @param ResponseFactory $responseFactory
     */
    public function __construct(AnetSDKRequestFactoryInterface $requestFactory, ResponseFactory $responseFactory)
    {
        $this->requestFactory = $requestFactory;
        $this->responseFactory = $responseFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function send(string $hostAddress, string $requestType, array $options = [])
    {
        $request = $this->requestFactory->createRequest($requestType, $options);
        $controller = $this->requestFactory->createController($request);

        $apiResponse = $controller->executeWithApiResponse($hostAddress);

        if ($apiResponse instanceof ErrorResponse) {
            throw new \LogicException('Authorize.Net SDK API returned ErrorResponse');
        }

        return $this->responseFactory->createResponse($apiResponse);
    }
}
