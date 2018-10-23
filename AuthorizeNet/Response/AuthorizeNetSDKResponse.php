<?php

namespace Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Response;

use JMS\Serializer\Serializer;
use net\authorize\api\contract\v1\ANetApiResponseType;

/**
 * General Response class to represent AuthorizeNet API Response
 */
class AuthorizeNetSDKResponse implements ResponseInterface
{
    /**
     * @var ANetApiResponseType
     */
    protected $apiResponse;

    /**
     * @var array|null
     */
    protected $apiResponseSerialized;

    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * @param Serializer $serializer
     * @param ANetApiResponseType $apiResponse
     */
    public function __construct(Serializer $serializer, ANetApiResponseType $apiResponse)
    {
        $this->serializer = $serializer;
        $this->apiResponse = $apiResponse;
    }

    /**
     * {@inheritdoc}
     */
    public function isSuccessful()
    {
        return $this->apiResponse->getMessages()->getResultCode() === 'Ok';
    }

    /**
     * {@inheritdoc}
     */
    public function getReference()
    {
        return $this->apiResponse->getRefId();
    }

    /**
     * {@inheritdoc}
     */
    public function getMessage()
    {
        return $this->isSuccessful() ?
            $this->getSuccessMessage() :
            $this->getErrorMessage();
    }

    /**
     * @return null|string
     */
    protected function getSuccessMessage()
    {
        $messages = $this->collectMessages();

        return empty($messages) ? null : implode(';  ', $messages);
    }

    /**
     * @return null|string
     */
    protected function getErrorMessage()
    {
        $errorMessages = $this->collectMessages();

        return empty($errorMessages) ? null : implode(';  ', $errorMessages);
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        if ($this->apiResponseSerialized === null) {
            $this->apiResponseSerialized = $this->cleanup($this->serializer->toArray($this->apiResponse));
        }

        return $this->apiResponseSerialized;
    }

    /**
     * @param array $response
     * @return array
     */
    protected function cleanup(array $response)
    {
        foreach ($response as $key => $value) {
            if (is_array($value)) {
                $response[$key] = $this->cleanup($value);
            }
            if ($response[$key] === [] || $response[$key] === '') {
                unset($response[$key]);
            }
        }

        return $response;
    }

    /**
     * @return array
     */
    protected function collectMessages()
    {
        $messages = [];
        foreach ($this->apiResponse->getMessages()->getMessage() as $message) {
            $messages[] = "({$message->getCode()}) {$message->getText()}";
        }

        return $messages;
    }
}
