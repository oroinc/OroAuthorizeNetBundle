<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Response;

use JMS\Serializer\Serializer;
use net\authorize\api\contract\v1 as AnetAPI;

/**
 * Response factory to create appropriate Response by apiResponse
 */
class ResponseFactory
{
    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * @param Serializer $serializer
     */
    public function __construct(Serializer $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @param AnetAPI\ANetApiResponseType $apiResponse
     * @return ResponseInterface
     */
    public function createResponse(AnetAPI\ANetApiResponseType $apiResponse)
    {
        if ($apiResponse instanceof AnetAPI\CreateTransactionResponse) {
            $response = new AuthorizeNetSDKTransactionResponse($this->serializer, $apiResponse);
        } else {
            $response = new AuthorizeNetSDKResponse($this->serializer, $apiResponse);
        }

        return $response;
    }
}
