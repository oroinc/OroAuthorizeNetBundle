<?php

namespace Oro\Bundle\AuthorizeNetBundle\Method;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Gateway;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Request\GetTransactionDetailsRequest;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Response\AuthorizeNetSDKResponse;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Response\AuthorizeNetSDKTransactionResponse;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Response\ResponseInterface;
use Oro\Bundle\AuthorizeNetBundle\Event\SDKResponseReceivedEvent;
use Oro\Bundle\AuthorizeNetBundle\Event\TransactionResponseReceivedEvent;
use Oro\Bundle\AuthorizeNetBundle\Exception\TransactionLimitReachedException;
use Oro\Bundle\AuthorizeNetBundle\Method\Config\AuthorizeNetConfigInterface;
use Oro\Bundle\AuthorizeNetBundle\Method\Option\Resolver\MethodOptionResolverInterface;
use Oro\Bundle\PaymentBundle\Context\PaymentContextInterface;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;
use Oro\Bundle\PaymentBundle\Method\PaymentMethodInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Payment method realization (Authorize.Net API)
 *  use internal MethodOptionResolverInterface to fetch/prepare options for Gateway
 *  saves options to transaction.request
 *  use Gateway to send request to Authorize.Net
 *  checks api response and format array Response for Oro Payment logic
 */
class AuthorizeNetPaymentMethod implements PaymentMethodInterface
{
    use LoggerAwareTrait;

    /**
     * Verify action for checking data about the transaction.
     */
    public const VERIFY = 'verify';

    /** @var Gateway */
    protected $gateway;

    /** @var AuthorizeNetConfigInterface */
    protected $config;

    /** @var RequestStack */
    protected $requestStack;

    /** @var MethodOptionResolverInterface */
    protected $methodOptionResolver;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    protected static $matchedEvent = [
        AuthorizeNetSDKTransactionResponse::class => TransactionResponseReceivedEvent::class,
        AuthorizeNetSDKResponse::class => SDKResponseReceivedEvent::class
    ];

    /**
     * @param Gateway $gateway
     * @param AuthorizeNetConfigInterface $config
     * @param RequestStack $requestStack
     * @param MethodOptionResolverInterface $methodOptionResolver
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        Gateway $gateway,
        AuthorizeNetConfigInterface $config,
        RequestStack $requestStack,
        MethodOptionResolverInterface $methodOptionResolver,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->gateway = $gateway;
        $this->config = $config;
        $this->requestStack = $requestStack;
        $this->methodOptionResolver = $methodOptionResolver;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return $this->config->getPaymentMethodIdentifier();
    }

    /**
     * {@inheritdoc}
     */
    public function isApplicable(PaymentContextInterface $context)
    {
        $request = $this->requestStack->getCurrentRequest();

        return !$request || $request->isSecure();
    }

    /**
     * {@inheritdoc}
     */
    public function supports($actionName)
    {
        return in_array(
            $actionName,
            [self::AUTHORIZE, self::CAPTURE, self::CHARGE, self::PURCHASE, self::VALIDATE, self::VERIFY],
            true
        );
    }

    /**
     * {@inheritdoc}
     */
    public function execute($action, PaymentTransaction $paymentTransaction)
    {
        if (!$this->supports($action)) {
            throw new \InvalidArgumentException(sprintf('Unsupported action "%s"', $action));
        }

        $this->gateway->setTestMode($this->config->isTestMode());

        try {
            return $this->{$action}($paymentTransaction) ?: [];
        } catch (TransactionLimitReachedException $e) {
            return [];
        }
    }

    /**
     * @param PaymentTransaction $paymentTransaction
     * @return array
     */
    protected function validate(PaymentTransaction $paymentTransaction)
    {
        $paymentTransaction
            ->setAmount(0)
            ->setCurrency('')
            ->setAction(PaymentMethodInterface::VALIDATE)
            ->setActive(true)
            ->setSuccessful(true);

        return ['successful' => true];
    }

    /**
     * @param PaymentTransaction $paymentTransaction
     * @return array
     */
    protected function purchase(PaymentTransaction $paymentTransaction)
    {
        $options = $this->methodOptionResolver->resolvePurchase($this->config, $paymentTransaction);
        $action = $this->config->getPurchaseAction();
        $paymentTransaction->setRequest($options)->setAction($action);

        return $this->executePaymentAction($paymentTransaction->getAction(), $options, $paymentTransaction);
    }

    /**
     * @param PaymentTransaction $paymentTransaction
     * @return array
     */
    protected function authorize(PaymentTransaction $paymentTransaction)
    {
        $options = $this->methodOptionResolver->resolveAuthorize($this->config, $paymentTransaction);

        return $this->executePaymentAction(self::AUTHORIZE, $options, $paymentTransaction);
    }

    /**
     * @param PaymentTransaction $paymentTransaction
     * @return array
     */
    protected function charge(PaymentTransaction $paymentTransaction)
    {
        $options = $this->methodOptionResolver->resolveCharge($this->config, $paymentTransaction);

        return $this->executePaymentAction(self::CHARGE, $options, $paymentTransaction);
    }

    /**
     * @param PaymentTransaction $paymentTransaction
     * @return array
     */
    protected function capture(PaymentTransaction $paymentTransaction)
    {
        $authorizeTransaction = $paymentTransaction->getSourcePaymentTransaction();
        if (!$authorizeTransaction) {
            $paymentTransaction
                ->setSuccessful(false)
                ->setActive(false);

            return ['successful' => false];
        }

        $options = $this->methodOptionResolver->resolveCapture($this->config, $paymentTransaction);
        $paymentTransaction->setRequest($options);
        $result = $this->executePaymentAction(self::CAPTURE, $options, $paymentTransaction);

        $paymentTransaction->setActive(false);
        $authorizeTransaction->setActive(!$paymentTransaction->isSuccessful());

        return $result;
    }

    /**
     * @param PaymentTransaction $paymentTransaction
     * @return array
     */
    protected function verify(PaymentTransaction $paymentTransaction)
    {
        $options = $this->methodOptionResolver->resolveVerify($this->config, $paymentTransaction);
        $response = $this->executeAction(self::VERIFY, $options, $paymentTransaction);

        $data = $response->getData();
        $data['successful'] = $response->isSuccessful();

        $paymentTransaction->setRequest($options);
        $paymentTransaction->setReference($options['original_transaction']);

        return $data;
    }

    /**
     * @param string $action
     * @param array $options
     * @param PaymentTransaction $paymentTransaction
     * @return array
     */
    protected function executePaymentAction(string $action, array $options, PaymentTransaction $paymentTransaction)
    {
        $response = $this->executeAction($action, $options, $paymentTransaction);

        return [
            'message' => $response->getMessage(),
            'successful' => $response->isSuccessful(),
        ];
    }

    /**
     * @param string $action
     * @param array $options
     * @param PaymentTransaction $paymentTransaction
     * @return ResponseInterface
     */
    protected function executeAction(string $action, array $options, PaymentTransaction $paymentTransaction)
    {
        $response = $this->gateway->request($this->getTransactionType($action), $options);

        $this->dispatchResponseReceivedEvent($response, $paymentTransaction);

        $paymentTransaction
            ->setSuccessful($response->isSuccessful())
            ->setActive($response->isActive())
            ->setReference($response->getReference())
            ->setResponse($response->getData());

        if ($this->logger && !$response->isSuccessful()) {
            $this->logger->critical($response->getMessage());
        }

        return $response;
    }

    /**
     * @param string $action
     * @return string
     * @throws \InvalidArgumentException
     */
    protected function getTransactionType($action): string
    {
        switch ($action) {
            case self::CAPTURE:
                return Option\Transaction::CAPTURE;
                break;
            case self::CHARGE:
                return Option\Transaction::CHARGE;
                break;
            case self::AUTHORIZE:
                return Option\Transaction::AUTHORIZE;
                break;
            case self::VERIFY:
                return GetTransactionDetailsRequest::REQUEST_TYPE;
                break;
            default:
                throw new \InvalidArgumentException(sprintf('Unsupported action "%s"', $action));
        }
    }

    /**
     * @param ResponseInterface $response
     * @param PaymentTransaction $paymentTransaction
     */
    protected function dispatchResponseReceivedEvent(
        ResponseInterface $response,
        PaymentTransaction $paymentTransaction
    ) {
        $className = get_class($response);
        if (!$className) {
            return;
        }

        $this->eventDispatcher->dispatch(
            new self::$matchedEvent[$className]($response, $paymentTransaction),
            self::$matchedEvent[$className]::NAME
        );
    }
}
