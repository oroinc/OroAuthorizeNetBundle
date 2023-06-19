<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Response;

use JMS\Serializer\ArrayTransformerInterface;
use net\authorize\api\contract\v1\ANetApiResponseType;
use net\authorize\api\contract\v1\MessagesType;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Response\AuthorizeNetSDKResponse;

class AuthorizeNetSDKResponseTest extends \PHPUnit\Framework\TestCase
{
    /** @var ArrayTransformerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $serializer;

    /** @var ANetApiResponseType|\PHPUnit\Framework\MockObject\MockObject */
    private $apiResponse;

    /** @var AuthorizeNetSDKResponse */
    private $authorizeNetSdkResponse;

    protected function setUp(): void
    {
        $this->serializer = $this->createMock(ArrayTransformerInterface::class);
        $this->apiResponse = $this->createMock(ANetApiResponseType::class);
        $this->authorizeNetSdkResponse = new AuthorizeNetSDKResponse($this->serializer, $this->apiResponse);
    }

    public function testIsSuccessfulWithErrorResultCode()
    {
        $messages = new MessagesType();
        $messages->setResultCode('Error');
        $this->apiResponse->expects($this->once())
            ->method('getMessages')
            ->willReturn($messages);

        $this->assertFalse($this->authorizeNetSdkResponse->isSuccessful());
    }

    public function testIsSuccessfulWithValidResponse()
    {
        $messages = new MessagesType();
        $messages->setResultCode('Ok');
        $this->apiResponse->expects($this->once())
            ->method('getMessages')
            ->willReturn($messages);

        $this->assertTrue($this->authorizeNetSdkResponse->isSuccessful());
    }

    public function tesIsActiveWithErrorResultCode()
    {
        $messages = new MessagesType();
        $messages->setResultCode('Error');
        $this->apiResponse->expects($this->once())
            ->method('getMessages')
            ->willReturn($messages);

        $this->assertFalse($this->authorizeNetSdkResponse->isActive());
    }

    public function tesIsActiveWithValidResponse()
    {
        $messages = new MessagesType();
        $messages->setResultCode('Ok');
        $this->apiResponse->expects($this->once())
            ->method('getMessages')
            ->willReturn($messages);

        $this->assertTrue($this->authorizeNetSdkResponse->isActive());
    }

    public function testGetReference()
    {
        $refId = '111';
        $this->apiResponse->expects($this->once())
            ->method('getRefId')
            ->willReturn($refId);

        $this->assertSame($refId, $this->authorizeNetSdkResponse->getReference());
    }

    public function testGetSuccessMessage()
    {
        $apiMessage = (new MessagesType\MessageAType())->setCode(255)->setText('Will be force with you!');
        $apiMessageType = (new MessagesType())->setResultCode('Ok')->setMessage([$apiMessage]);

        $this->apiResponse->expects($this->exactly(2))
            ->method('getMessages')
            ->willReturn($apiMessageType);

        $this->assertSame(
            '(255) Will be force with you!',
            $this->authorizeNetSdkResponse->getMessage()
        );
    }

    public function testGetErrorMessage()
    {
        $apiMessage = (new MessagesType\MessageAType())->setCode(408)->setText('The Dark Side is strong in you!');
        $apiMessageType = (new MessagesType())->setResultCode('Error')->setMessage([$apiMessage]);

        $this->apiResponse->expects($this->exactly(2))
            ->method('getMessages')
            ->willReturn($apiMessageType);

        $this->assertSame(
            '(408) The Dark Side is strong in you!',
            $this->authorizeNetSdkResponse->getMessage()
        );
    }

    /**
     * @dataProvider responseArrayDataProvider
     */
    public function testGetData(array $entryData, array $expectedData)
    {
        $this->serializer->expects($this->once())
            ->method('toArray')
            ->with($this->apiResponse)
            ->willReturn($entryData);

        $this->assertSame($expectedData, $this->authorizeNetSdkResponse->getData());
    }

    public function responseArrayDataProvider(): array
    {
        return [
            [
                'entry_data' => [
                    'transId' => 1,
                    'responseCode' => 2,
                    'message' => ['success' => 'this is success message', 'empty_array' => []],
                    'empty_row' => '',
                    'empty_array' => [],
                    'zero_value' => 0,
                ],
                'expected_data' => [
                    'transId' => 1,
                    'responseCode' => 2,
                    'message' => ['success' => 'this is success message'],
                    'zero_value' => 0,
                ],
            ],
        ];
    }
}
