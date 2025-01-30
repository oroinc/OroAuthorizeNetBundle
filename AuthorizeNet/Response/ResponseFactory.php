<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Response;

use JMS\Serializer\ArrayTransformerInterface;
use JMS\Serializer\SerializerBuilder;
use net\authorize\api\contract\v1 as AnetAPI;

/**
 * The factory to create appropriate response object by Authorize.Net API response.
 */
class ResponseFactory
{
    private ?ArrayTransformerInterface $serializer;

    public function __construct(?ArrayTransformerInterface $serializer = null)
    {
        $this->serializer = $serializer;
    }

    public function createResponse(AnetAPI\ANetApiResponseType $apiResponse): ResponseInterface
    {
        if ($apiResponse instanceof AnetAPI\CreateTransactionResponse) {
            return new AuthorizeNetSDKTransactionResponse($this->getSerializer(), $apiResponse);
        }

        return new AuthorizeNetSDKResponse($this->getSerializer(), $apiResponse);
    }

    private function getSerializer(): ArrayTransformerInterface
    {
        if (null === $this->serializer) {
            $this->serializer = SerializerBuilder::create()->build();
        }

        return $this->serializer;
    }
}
