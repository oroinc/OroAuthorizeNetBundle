<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Behat\Mock\AuthorizeNet\Client\Factory\Api;

use net\authorize\api\contract\v1\AnetApiRequestType;
use net\authorize\api\contract\v1\CreateCustomerProfileRequest;
use net\authorize\api\contract\v1\CreateCustomerProfileResponse;
use net\authorize\api\contract\v1\MessagesType;

class CreateCustomerProfileControllerMock extends AbstractControllerMock
{
    /** @var string */
    public const REGISTERED_CUSTOMER_PROFILE_ID = '1';

    /** @var AnetApiRequestType */
    private $request;

    public function __construct(CreateCustomerProfileRequest $request)
    {
        $this->request = $request;
    }

    /**
     * @param null|string $endPoint
     * @return CreateCustomerProfileResponse
     */
    #[\Override]
    public function executeWithApiResponse($endPoint = null): CreateCustomerProfileResponse
    {
        $response = new CreateCustomerProfileResponse();

        $email = 'AmandaRCole@example.org';
        if ($email !== $this->request->getProfile()->getEmail()) {
            $errorMessage = 'Incorrect email given while try to create customer profile, expecting "' . $email . '"';

            if (null === $this->request->getProfile()->getEmail()) {
                $errorMessage .= ', but got nothing!';
            } else {
                $errorMessage .= sprintf(', but got %!', $this->request->getProfile()->getEmail());
            }

            throw new \RuntimeException($errorMessage);
        }

        $response->setCustomerProfileId(
            self::REGISTERED_CUSTOMER_PROFILE_ID
        );
        $messages = new MessagesType();
        $messages->setResultCode('Ok');
        $response->setMessages($messages);

        return $response;
    }
}
