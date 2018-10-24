<?php

namespace Oro\Bundle\AuthorizeNetBundle\Method;

use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Gateway;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Option;
use Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Response\AuthorizeNetSDKTransactionResponse;
use Oro\Bundle\AuthorizeNetBundle\Event\TransactionResponseReceivedEvent;
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
            [self::AUTHORIZE, self::CAPTURE, self::CHARGE, self::PURCHASE, self::VALIDATE],
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

        return $this->{$action}($paymentTransaction) ?: [];
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
     * @param string $action
     * @param array $options
     * @param PaymentTransaction $paymentTransaction
     * @return array
     */
    protected function executePaymentAction(string $action, array $options, PaymentTransaction $paymentTransaction)
    {
        $response = $this->gateway->request($this->getTransactionType($action), $options);

        $this->dispatchTransactionResponseReceivedEvent($response, $paymentTransaction);

        $paymentTransaction
            ->setSuccessful($response->isSuccessful())
            ->setActive($response->isSuccessful())
            ->setReference($response->getReference())
            ->setResponse($response->getData());

        if ($this->logger && !$response->isSuccessful()) {
            $this->logger->critical($response->getMessage());
        }

        return [
            'message' => $response->getMessage(),
            'successful' => $response->isSuccessful(),
        ];
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
            default:
                throw new \InvalidArgumentException(sprintf('Unsupported action "%s"', $action));
        }
    }

    /**
     * @param AuthorizeNetSDKTransactionResponse $response
     * @param PaymentTransaction $paymentTransaction
     */
    protected function dispatchTransactionResponseReceivedEvent(
        AuthorizeNetSDKTransactionResponse $response,
        PaymentTransaction $paymentTransaction
    ) {
        $this->eventDispatcher->dispatch(
            TransactionResponseReceivedEvent::NAME,
            new TransactionResponseReceivedEvent($response, $paymentTransaction)
        );
    }
}
