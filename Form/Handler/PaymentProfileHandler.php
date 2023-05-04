<?php

namespace Oro\Bundle\AuthorizeNetBundle\Form\Handler;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\AuthorizeNetBundle\Entity\CustomerPaymentProfile;
use Oro\Bundle\AuthorizeNetBundle\Entity\CustomerProfile;
use Oro\Bundle\AuthorizeNetBundle\Helper\RequestSender;
use Oro\Bundle\AuthorizeNetBundle\Model\DTO\PaymentProfileDTO;
use Oro\Bundle\AuthorizeNetBundle\Provider\CustomerProfileProvider;
use Oro\Bundle\AuthorizeNetBundle\Provider\IntegrationProvider;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\FormBundle\Form\Handler\FormHandler;
use Oro\Bundle\FormBundle\Form\Handler\RequestHandlerTrait;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessor;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Payment profile form handler
 */
class PaymentProfileHandler extends FormHandler
{
    use RequestHandlerTrait;

    /** @var TokenAccessor */
    private $tokenAccessor;

    /** @var RequestStack */
    private $requestStack;

    /** @var RequestSender */
    private $requestSender;

    /** @var TranslatorInterface */
    private $translator;

    /** @var IntegrationProvider */
    private $integrationProvider;

    /** @var CustomerProfileProvider */
    private $customerProfileProvider;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        DoctrineHelper $doctrineHelper,
        TokenAccessor $tokenAccessor,
        RequestStack $requestStack,
        RequestSender $requestSender,
        TranslatorInterface $translator,
        IntegrationProvider $integrationProvider,
        CustomerProfileProvider $customerProfileProvider
    ) {
        parent::__construct($eventDispatcher, $doctrineHelper);
        $this->tokenAccessor = $tokenAccessor;
        $this->requestStack = $requestStack;
        $this->requestSender = $requestSender;
        $this->translator = $translator;
        $this->integrationProvider = $integrationProvider;
        $this->customerProfileProvider = $customerProfileProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function process($data, FormInterface $form, Request $request)
    {
        $this->assertIsApplicableData($data);

        /** @var PaymentProfileDTO $paymentProfileDTO */
        $paymentProfileDTO = $data;
        $paymentProfile = $paymentProfileDTO->getProfile();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->savePaymentProfile($paymentProfileDTO);

                return true;
            } catch (\LogicException $e) {
                $errorMessage = $this->translator->trans(
                    'oro.authorize_net.frontend.payment_profile.message.not_saved',
                    ['%reason%' => $e->getMessage()]
                );
                $this->requestStack->getSession()->getFlashBag()->add('error', $errorMessage);
            }
        } elseif (!$form->isSubmitted() && $paymentProfile->getId()) { //fill up dto with api response
            $paymentProfileData = $this->requestSender->getCustomerPaymentProfile($paymentProfile);
            $addressDTO = $this->requestSender->getPaymentProfileAddressDTO($paymentProfileData['bill_to']);
            $maskedDataDTO = $this->requestSender->getPaymentProfileMaskedDataDTO($paymentProfileData['payment']);
            $paymentProfileDTO->setAddress($addressDTO);
            $paymentProfileDTO->setMaskedData($maskedDataDTO);
            $form->setData($paymentProfileDTO);
        }

        return false;
    }

    /**
     * @param mixed $data
     */
    protected function assertIsApplicableData($data)
    {
        if (!$data instanceof PaymentProfileDTO) {
            throw new \InvalidArgumentException(sprintf(
                'Data should be instance of %s, but %s is given',
                PaymentProfileDTO::class,
                \is_object($data) ? \get_class($data) : \gettype($data)
            ));
        }
    }

    /**
     * @param PaymentProfileDTO $paymentProfileDTO
     * @return CustomerPaymentProfile
     */
    private function savePaymentProfile(PaymentProfileDTO $paymentProfileDTO)
    {
        $paymentProfile = $paymentProfileDTO->getProfile();

        $customerUser = $this->tokenAccessor->getUser();

        if (!$customerUser instanceof CustomerUser) {
            throw new AccessDeniedException();
        }

        if (!$customerProfile = $paymentProfile->getCustomerProfile()) {//need to find/create CustomerProfile
            $customerProfile = $this->getCustomerProfile($customerUser);
        }

        $paymentProfile->setCustomerUser($customerUser);
        $paymentProfile->setCustomerProfile($customerProfile);
        $paymentProfileType = $paymentProfile->getType();

        if ($paymentProfile->isDefault()) {
            foreach ($customerProfile->getPaymentProfilesByType($paymentProfileType) as $customerPaymentProfile) {
                $customerPaymentProfile->setDefault(false);
            }
            $paymentProfile->setDefault(true);
        }

        if ($paymentProfile->getCustomerPaymentProfileId()) {
            $this->requestSender->updateCustomerPaymentProfile($paymentProfileDTO);
        } else {
            $customerPaymentProfileId = $this->requestSender->createCustomerPaymentProfile($paymentProfileDTO);
            $paymentProfile->setCustomerPaymentProfileId($customerPaymentProfileId);
        }

        $manager = $this->doctrineHelper->getEntityManager($paymentProfile);
        $manager->persist($paymentProfile);
        $manager->flush();

        return $paymentProfile;
    }

    /**
     * @param CustomerUser $customerUser
     * @return CustomerProfile
     */
    private function getCustomerProfile(CustomerUser $customerUser)
    {
        $customerProfile = $this->customerProfileProvider->findCustomerProfile($customerUser);

        if (!$customerProfile) {
            $customerProfile = $this->createCustomerProfile();
        }

        return $customerProfile;
    }

    /**
     * @return CustomerProfile
     */
    private function createCustomerProfile()
    {
        $customerUser = $this->tokenAccessor->getUser();
        $integration = $this->integrationProvider->getIntegration();

        $customerProfile = new CustomerProfile();
        $customerProfile->setIntegration($integration);
        $customerProfile->setCustomerUser($customerUser);

        $customerProfileId = $this->requestSender->createCustomerProfile($customerProfile);
        $customerProfile->setCustomerProfileId($customerProfileId);

        /** @var EntityManager $manager */
        $manager = $this->doctrineHelper->getEntityManager($customerProfile);
        $manager->persist($customerProfile);
        $manager->flush($customerProfile);

        return $customerProfile;
    }
}
