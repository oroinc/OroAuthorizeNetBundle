<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Functional\AuthorizeNet\Response;

use net\authorize\api\contract\v1 as AnetAPI;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Response\ResponseFactory;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class AuthorizeNetSDKResponseTest extends WebTestCase
{
    #[\Override]
    protected function setUp(): void
    {
        $this->initClient([], $this->generateBasicAuthHeader());
    }

    private function getResponseFactory(): ResponseFactory
    {
        return self::getContainer()->get('oro_authorize_net.authorize_net.response.response_factory');
    }

    public function testResponseDataSerialization(): void
    {
        $apiResponse = new AnetAPI\ErrorResponse();
        $apiResponse->setRefId('test_ref_id');
        $apiResponse->setSessionToken('test_session');
        $apiResponseMessages = new AnetAPI\MessagesType();
        $apiResponseMessages->setResultCode('test_result_code');
        $apiResponseMessage1 = new AnetAPI\MessagesType\MessageAType();
        $apiResponseMessage1->setCode('test_message_code1');
        $apiResponseMessage1->setText('test message text 1');
        $apiResponseMessage2 = new AnetAPI\MessagesType\MessageAType();
        $apiResponseMessage2->setCode('test_message_code2');
        $apiResponseMessage2->setText('');
        $apiResponseMessage3 = new AnetAPI\MessagesType\MessageAType();
        $apiResponseMessage3->setCode('test_message_code3');
        $apiResponseMessages->setMessage([$apiResponseMessage1, $apiResponseMessage2, $apiResponseMessage3]);
        $apiResponse->setMessages($apiResponseMessages);

        $response = $this->getResponseFactory()->createResponse($apiResponse);

        self::assertEquals(
            [
                'ref_id'        => 'test_ref_id',
                'session_token' => 'test_session',
                'messages'      => [
                    'result_code' => 'test_result_code',
                    'message'     => [
                        ['code' => 'test_message_code1', 'text' => 'test message text 1'],
                        ['code' => 'test_message_code2'],
                        ['code' => 'test_message_code3']
                    ]
                ]
            ],
            $response->getData()
        );
    }
}
