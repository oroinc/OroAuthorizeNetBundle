<?php

namespace Oro\Bundle\AuthorizeNetBundle\Method\Option\Provider;

use Oro\Bundle\AuthorizeNetBundle\Entity\CustomerPaymentProfile;
use Oro\Bundle\AuthorizeNetBundle\Entity\CustomerProfile;
use Oro\Bundle\AuthorizeNetBundle\Helper\MerchantCustomerIdGenerator;
use Oro\Bundle\AuthorizeNetBundle\Method\Config\AuthorizeNetConfigInterface;
use Oro\Bundle\AuthorizeNetBundle\Provider\CustomerProfileProvider;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\OrderBundle\Entity\Order;
use Oro\Bundle\OrderBundle\Entity\OrderLineItem;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Facade, allow to fetch various kind of options, from different sources:
 *   from Authorize.Net config
 *   from database
 *   from paymentTransaction->additionalData
 */
class MethodOptionProvider implements MethodOptionProviderInterface
{
    // Authorize.Net solution id
    public const SOLUTION_ID = 'AAA171478';
    private const AMOUNT_PRECISION = 2;

    private const PARAM_DATA_DESCRIPTOR = 'dataDescriptor';
    private const PARAM_DATA_VALUE = 'dataValue';

    private const PARAM_PROFILE_ID = 'profileId';
    private const PARAM_CARD_CODE = 'cvv';
    private const PARAM_CREATE_PROFILE = 'saveProfile';

    /** @var AuthorizeNetConfigInterface */
    private $config;

    /** @var PaymentTransaction */
    private $paymentTransaction;

    /** @var CustomerProfileProvider */
    private $customerProfileProvider;

    /** @var DoctrineHelper */
    private $doctrineHelper;

    /** @var MerchantCustomerIdGenerator */
    private $merchantCustomerIdGenerator;

    /** @var array */
    private $additionalData;

    /** @var RequestStack */
    private $requestStack;

    public function __construct(
        AuthorizeNetConfigInterface $config,
        PaymentTransaction $paymentTransaction,
        CustomerProfileProvider $customerProfileProvider,
        DoctrineHelper $doctrineHelper,
        MerchantCustomerIdGenerator $merchantCustomerIdGenerator,
        RequestStack $requestStack
    ) {
        $this->config = $config;
        $this->paymentTransaction = $paymentTransaction;
        $this->customerProfileProvider = $customerProfileProvider;
        $this->doctrineHelper = $doctrineHelper;
        $this->merchantCustomerIdGenerator = $merchantCustomerIdGenerator;
        $this->requestStack = $requestStack;
    }

    private function getCustomerPaymentProfileById(int $id): ?CustomerPaymentProfile
    {
        $repository = $this->doctrineHelper->getEntityRepository(CustomerPaymentProfile::class);

        /** @var CustomerPaymentProfile $profile */
        $profile = $repository->find($id);

        return $profile;
    }

    public function getSolutionId(): ?string
    {
        return $this->config->isTestMode() ? null : self::SOLUTION_ID;
    }

    public function getApiLoginId(): string
    {
        return $this->config->getApiLoginId();
    }

    public function getTransactionKey(): string
    {
        return $this->config->getTransactionKey();
    }

    public function getDataDescriptor(): string
    {
        return $this->getAdditionalDataField(self::PARAM_DATA_DESCRIPTOR);
    }

    public function getDataValue(): string
    {
        return $this->getAdditionalDataField(self::PARAM_DATA_VALUE);
    }

    public function isCustomerProfileExists(): bool
    {
        return null !== $this->getCustomerProfile();
    }

    public function getExistingCustomerProfileId(): string
    {
        $customerProfile = $this->getCustomerProfile();
        if (null === $customerProfile) {
            throw new \LogicException('Customer profile not exists');
        }

        return $customerProfile->getCustomerProfileId();
    }

    public function getExistingCustomerPaymentProfileId(): string
    {
        $profileId = $this->getProfileId();
        if (null === $profileId) {
            throw new \LogicException('profileId is required');
        }

        $paymentProfile = $this->getCustomerPaymentProfileById($profileId);
        if (null === $paymentProfile) {
            throw new \LogicException(
                sprintf('Can not find customer payment profile with id #%d', $profileId)
            );
        }

        $customerProfile = $paymentProfile->getCustomerProfile();
        $frontendOwner = $this->paymentTransaction->getFrontendOwner();
        if (null !== $frontendOwner) {
            if ($frontendOwner->getId() !== $customerProfile->getCustomerUser()->getId()) {
                throw new AccessDeniedException('Access to customer profile denied');
            }
        }

        return $paymentProfile->getCustomerPaymentProfileId();
    }

    public function getGeneratedNewCustomerProfileId(): string
    {
        if ($this->isCustomerProfileExists()) {
            throw new \LogicException('Customer profile already exists');
        }

        $frontendOwner = $this->paymentTransaction->getFrontendOwner();
        if (null === $frontendOwner) {
            throw new \LogicException('Customer User not defined');
        }

        return $this->merchantCustomerIdGenerator->generate($this->config->getIntegrationId(), $frontendOwner->getId());
    }

    public function getEmail(): ?string
    {
        $frontendOwner = $this->paymentTransaction->getFrontendOwner();
        if (null === $frontendOwner) {
            return null;
        }

        return $frontendOwner->getEmail();
    }

    public function getProfileId(): ?int
    {
        return $this->getAdditionalDataField(self::PARAM_PROFILE_ID, false);
    }

    public function getCardCode(): ?string
    {
        return $this->getAdditionalDataField(self::PARAM_CARD_CODE, false);
    }

    public function getCreateProfile(): ?bool
    {
        $createProfile = $this->getAdditionalDataField(self::PARAM_CREATE_PROFILE, false);
        if (null === $createProfile) {
            return null;
        }

        return (bool)$createProfile;
    }

    public function getAmount(): float
    {
        return round($this->paymentTransaction->getAmount(), self::AMOUNT_PRECISION);
    }

    public function getCurrency(): string
    {
        return $this->paymentTransaction->getCurrency();
    }

    public function getOriginalTransaction(): ?string
    {
        return $this->paymentTransaction->getReference();
    }

    private function getCustomerProfile(): ?CustomerProfile
    {
        return $this->customerProfileProvider->findCustomerProfile(
            $this->paymentTransaction->getFrontendOwner()
        );
    }

    /**
     * @param string $fieldName
     * @param bool $required
     *
     * @return mixed
     *
     * @throws \LogicException
     */
    private function getAdditionalDataField(string $fieldName, bool $required = true)
    {
        $additionalData = $this->getAdditionalData();
        if ($required && !array_key_exists($fieldName, $additionalData)) {
            throw new \LogicException(sprintf(
                'Can not find field "%s" in additional data',
                $fieldName
            ));
        }

        return $additionalData[$fieldName] ?? null;
    }

    private function getAdditionalData(): array
    {
        if (null === $this->additionalData) {
            $this->additionalData = $this->extractAdditionalData();
        }

        return $this->additionalData;
    }

    /**
     * @throws \LogicException
     */
    private function extractAdditionalData(): array
    {
        $sourceTransaction = $this->paymentTransaction->getSourcePaymentTransaction();
        if (!$sourceTransaction) {
            throw new \LogicException('Cant extract sourceTransaction from transaction');
        }

        $options = $sourceTransaction->getTransactionOptions();
        if (!array_key_exists('additionalData', $options)) {
            throw new \LogicException('Cant extract additionalData from transaction');
        }

        $additionalData = \json_decode($options['additionalData'], true);

        if (!\is_array($additionalData)) {
            throw new \LogicException('Additional data must be an array');
        }

        return $additionalData;
    }

    /**
     * @return object
     */
    private function getEntity()
    {
        return $this->doctrineHelper->getEntityReference(
            $this->paymentTransaction->getEntityClass(),
            $this->paymentTransaction->getEntityIdentifier()
        );
    }

    /**
     * @return null|OrderLineItem[]
     */
    public function getLineItems(): ?array
    {
        $entity = $this->getEntity();
        $lineItems = null;

        if ($entity instanceof Order) {
            $lineItems = $entity->getLineItems()->toArray();
        }

        return $lineItems;
    }

    public function getInvoiceNumber(): ?string
    {
        $entity = $this->getEntity();
        $invoiceNumber = null;

        if ($entity instanceof Order) {
            $invoiceNumber = (string) $entity->getIdentifier();
        }

        return $invoiceNumber;
    }

    public function isCIMEnabled(): bool
    {
        return $this->config->isEnabledCIM();
    }

    public function getClientIp(): ?string
    {
        $request = $this->requestStack->getCurrentRequest();
        if ($request) {
            return $request->getClientIp();
        }

        return null;
    }
}
