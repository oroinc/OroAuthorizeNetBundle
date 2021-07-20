<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\AuthorizeNet\Response;

use JMS\Serializer\ArrayTransformerInterface;
use net\authorize\api\contract\v1\CreateTransactionResponse;
use net\authorize\api\contract\v1\MessagesType;
use net\authorize\api\contract\v1\TransactionResponseType;
use net\authorize\api\contract\v1\TransactionResponseType\ErrorsAType\ErrorAType as TransactionErrorMessage;
use net\authorize\api\contract\v1\TransactionResponseType\MessagesAType\MessageAType as TransactionMessage;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Response\AuthorizeNetSDKResponse;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Response\AuthorizeNetSDKTransactionResponse;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AuthorizeNetSDKTransactionResponseTest extends TestCase
{
    /** @var ArrayTransformerInterface|MockObject */
    protected $serializer;

    /** @var CreateTransactionResponse|MockObject */
    protected $apiResponse;

    /** @var AuthorizeNetSDKResponse */
    protected $authorizeNetSdkResponse;

    protected function setUp(): void
    {
        $this->serializer = $this->createMock(ArrayTransformerInterface::class);
        $this->apiResponse = $this->createMock(CreateTransactionResponse::class);
        $this->authorizeNetSdkResponse = new AuthorizeNetSDKTransactionResponse($this->serializer, $this->apiResponse);
    }

    /**
     * @dataProvider transactionDataProvider
     * @param bool $expectedSuccessful
     * @param bool $expectedActive
     * @param mixed $transactionResponse
     */
    public function testIsSuccessful(bool $expectedSuccessful, bool $expectedActive, $transactionResponse): void
    {
        $this->apiResponse->expects($this->exactly(2))
            ->method('getTransactionResponse')
            ->willReturn($transactionResponse);

        $this->assertEquals($expectedSuccessful, $this->authorizeNetSdkResponse->isSuccessful());
        $this->assertEquals($expectedActive, $this->authorizeNetSdkResponse->isActive());
    }

    public function transactionDataProvider(): array
    {
        return [
            'nullable_trans_response' => [
                'expectedSuccessful' => false,
                'expectedActive' => false,
                'transactionResponse' => null
            ],
            'zero_trans_response' => [
                'expectedSuccessful' => false,
                'expectedActive' => false,
                'transactionResponse' => (new TransactionResponseType())->setResponseCode(0)
            ],
            'integer_trans_response' => [
                'expectedSuccessful' => false,
                'expectedActive' => false,
                'transactionResponse' => (new TransactionResponseType())->setResponseCode(1)
            ],
            'valid_trans_response' => [
                'expectedSuccessful' => true,
                'expectedActive' => true,
                'transactionResponse' => (new TransactionResponseType())->setResponseCode('1')
            ],
            'valid_not_approved_trans_response' => [
                'expectedSuccessful' => true,
                'expectedActive' => false,
                'transactionResponse' => (new TransactionResponseType())->setResponseCode('4')
            ]
        ];
    }

    public function testGetReferenceWithEmptyTransactionResponse()
    {
        $this->apiResponse->expects($this->once())->method('getTransactionResponse')->willReturn(null);
        $this->assertNull($this->authorizeNetSdkResponse->getReference());
    }

    public function testGetReferenceWithValidResponse()
    {
        $transId = '111';
        $transactionResponse = new TransactionResponseType();
        $transactionResponse->setTransId($transId);
        $this->apiResponse->expects($this->once())->method('getTransactionResponse')
            ->willReturn($transactionResponse);

        $this->assertSame($transId, $this->authorizeNetSdkResponse->getReference());
    }

    public function testGetSuccessMessage()
    {
        $transactionMessage = (new TransactionMessage)->setCode(144)->setDescription('Luke is the best jedi');

        $transactionResponse = new TransactionResponseType();
        $transactionResponse->setResponseCode('1');
        $transactionResponse->setMessages([$transactionMessage]);

        $apiMessage = (new MessagesType\MessageAType)->setCode(255)->setText('Will be force with you!');
        $apiMessageType = (new MessagesType)->setResultCode('Ok')->setMessage([$apiMessage]);

        $this->apiResponse->expects($this->once())->method('getMessages')->willReturn($apiMessageType);
        $this->apiResponse->expects($this->exactly(2))->method('getTransactionResponse')
            ->willReturn($transactionResponse);

        $this->assertSame(
            '(255) Will be force with you!;  (144) Luke is the best jedi',
            $this->authorizeNetSdkResponse->getMessage()
        );
    }

    public function testGetErrorMessage()
    {
        $transactionError = (new TransactionErrorMessage)->setErrorCode(125)
            ->setErrorText('Darth Vader is coming for you');

        $transactionResponse = new TransactionResponseType();
        $transactionResponse->setResponseCode('0');
        $transactionResponse->setErrors([$transactionError]);

        $apiMessage = (new MessagesType\MessageAType)->setCode(408)->setText('The Dark Side is strong in you!');
        $apiMessageType = (new MessagesType)->setResultCode('Error')->setMessage([$apiMessage]);

        $this->apiResponse->expects($this->once())->method('getMessages')->willReturn($apiMessageType);
        $this->apiResponse->expects($this->exactly(2))->method('getTransactionResponse')
            ->willReturn($transactionResponse);

        $this->assertSame(
            '(408) The Dark Side is strong in you!;  (125) Darth Vader is coming for you',
            $this->authorizeNetSdkResponse->getMessage()
        );
    }

    /**
     * @dataProvider responseArrayDataProvider
     */
    public function testGetData($entryData, $expectedData)
    {
        $this->serializer->expects($this->once())->method('toArray')
            ->with($this->apiResponse)->willReturn($entryData);

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
