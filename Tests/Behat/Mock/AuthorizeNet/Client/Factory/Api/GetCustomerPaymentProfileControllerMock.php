<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Behat\Mock\AuthorizeNet\Client\Factory\Api;

use net\authorize\api\contract\v1\AnetApiRequestType;
use net\authorize\api\contract\v1\BankAccountMaskedType;
use net\authorize\api\contract\v1\CreditCardMaskedType;
use net\authorize\api\contract\v1\CustomerAddressType;
use net\authorize\api\contract\v1\CustomerPaymentProfileMaskedType;
use net\authorize\api\contract\v1\GetCustomerPaymentProfileRequest;
use net\authorize\api\contract\v1\GetCustomerPaymentProfileResponse;
use net\authorize\api\contract\v1\MessagesType;
use net\authorize\api\contract\v1\PaymentMaskedType;
use Oro\Bundle\AuthorizeNetBundle\Tests\Behat\Mock\Remote\Storage\PaymentProfileIDs;
use Oro\Bundle\AuthorizeNetBundle\Tests\Behat\Mock\Remote\Storage\PaymentProfileIDsAwareInterface;

class GetCustomerPaymentProfileControllerMock extends AbstractControllerMock implements
    PaymentProfileIDsAwareInterface
{
    /** @var AnetApiRequestType */
    protected $request;

    /** @var PaymentProfileIDs */
    protected $paymentProfileIdsStorage;

    /**
     * @param GetCustomerPaymentProfileRequest $request
     */
    public function __construct(GetCustomerPaymentProfileRequest $request)
    {
        $this->request = $request;
    }

    /**
     * @param PaymentProfileIDs $paymentProfileIdsStorage
     */
    public function setPaymentProfileIdsStorage(PaymentProfileIDs $paymentProfileIdsStorage)
    {
        $this->paymentProfileIdsStorage = $paymentProfileIdsStorage;
    }

    /**
     * @param null|string $endPoint
     * @return GetCustomerPaymentProfileResponse
     */
    public function executeWithApiResponse($endPoint = null)
    {
        $recordExists = $this->paymentProfileIdsStorage->exists(
            $this->request->getCustomerPaymentProfileId()
        );

        $response = new GetCustomerPaymentProfileResponse();

        if ($recordExists) {
            $customerPaymentProfile = new CustomerPaymentProfileMaskedType();
            $customerPaymentProfile->setCustomerPaymentProfileId($this->request->getCustomerPaymentProfileId());

            $payment = new PaymentMaskedType();
            $bankAccount = new BankAccountMaskedType();



            $creditCard = new CreditCardMaskedType();
            $creditCard->setCardNumber('5424000000000015');
            $creditCard->setExpirationDate('11/2027');

            $payment->setBankAccount($bankAccount);
            $payment->setCreditCard($creditCard);
            $customerPaymentProfile->setPayment($payment);

            $customerAddress = new CustomerAddressType();
            $customerAddress->setEmail('AmandaRCole@example.org');
            $customerAddress->setFirstName('Max');
            $customerAddress->setLastName('Maxwell');
            $customerAddress->setAddress('4576 Stonepot Road');
            $customerAddress->setCountry('DEU');
            $customerAddress->setCity('Berlin');
            $customerAddress->setState('Bayern');
            $customerAddress->setZip('10115');
            $customerPaymentProfile->setBillTo($customerAddress);

            $response->setPaymentProfile($customerPaymentProfile);

            $messages = new MessagesType();
            $messages->setResultCode('Ok');
        } else {
            $messages = new MessagesType();
            $messages->setResultCode('Error');
            $messages->addToMessage(
                (new MessagesType\MessageAType())
                    ->setCode('E00114')
                    ->setText('Incorrect payment profile id.')
            );
            $response->setMessages($messages);
        }

        $response->setMessages($messages);

        return $response;
    }
}
