<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Behat\Mock\AuthorizeNet\Client\Factory\Api;

use net\authorize\api\contract\v1\AnetApiRequestType;
use net\authorize\api\contract\v1\CreateCustomerProfileRequest;
use net\authorize\api\contract\v1\CreateCustomerProfileResponse;
use net\authorize\api\contract\v1\MessagesType;

class CreateCustomerProfileControllerMock extends AbstractControllerMock
{
    /** @var string */
    const REGISTERED_CUSTOMER_PROFILE_ID = '1';

    /** @var AnetApiRequestType */
    protected $request;

    /**
     * @param CreateCustomerProfileRequest $request
     */
    public function __construct(CreateCustomerProfileRequest $request)
    {
        $this->request = $request;
    }

    /**
     * @param null|string $endPoint
     * @return CreateCustomerProfileResponse
     */
    public function executeWithApiResponse($endPoint = null)
    {
        $response = new CreateCustomerProfileResponse();

        if ($this->request->getProfile()->getEmail() === 'AmandaRCole@example.org') {
            $response->setCustomerProfileId(
                self::REGISTERED_CUSTOMER_PROFILE_ID
            );

            $messages = new MessagesType();
            $messages->setResultCode('Ok');
        } else {
            $messages = new MessagesType();
            $messages->setResultCode('Error');
            $messages->addToMessage(
                (new MessagesType\MessageAType())
                    ->setCode('E00114')
                    ->setText('Invalid OTS Token.')
            );
        }

        $response->setMessages($messages);

        return $response;
    }
}
