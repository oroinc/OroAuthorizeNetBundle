<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Client;

use net\authorize\api\contract\v1\ErrorResponse;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Client\Factory\AnetSDKRequestFactoryInterface;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Response\ResponseFactory;
use Psr\Log\LoggerInterface;

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
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        AnetSDKRequestFactoryInterface $requestFactory,
        ResponseFactory $responseFactory,
        LoggerInterface $logger
    ) {
        $this->requestFactory = $requestFactory;
        $this->responseFactory = $responseFactory;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function send(string $hostAddress, string $requestType, array $options = [])
    {
        $request = $this->requestFactory->createRequest($requestType, $options);
        $controller = $this->requestFactory->createController($request);

        try {
            $apiResponse = $controller->executeWithApiResponse($hostAddress);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage(), [
                'exception' => \get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            throw new \LogicException('Unexpected Payment Gateway Error');
        }

        if ($apiResponse instanceof ErrorResponse) {
            throw new \LogicException('Authorize.Net SDK API returned ErrorResponse');
        }

        return $this->responseFactory->createResponse($apiResponse);
    }
}
