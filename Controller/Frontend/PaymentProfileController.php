<?php

namespace Oro\Bundle\AuthorizeNetBundle\Controller\Frontend;

use Oro\Bundle\AuthorizeNetBundle\Entity\CustomerPaymentProfile;
use Oro\Bundle\AuthorizeNetBundle\Entity\CustomerProfile;
use Oro\Bundle\AuthorizeNetBundle\Form\Handler\PaymentProfileHandler;
use Oro\Bundle\AuthorizeNetBundle\Handler\CustomerPaymentProfileDeleteHandler;
use Oro\Bundle\AuthorizeNetBundle\Handler\CustomerProfileDeleteHandler;
use Oro\Bundle\AuthorizeNetBundle\Layout\DataProvider\PaymentProfileDTOFormProvider;
use Oro\Bundle\AuthorizeNetBundle\Method\Config\AuthorizeNetConfigInterface;
use Oro\Bundle\AuthorizeNetBundle\Model\DTO\PaymentProfileDTO;
use Oro\Bundle\AuthorizeNetBundle\Provider\CIMEnabledIntegrationConfigProvider;
use Oro\Bundle\AuthorizeNetBundle\Provider\CustomerProfileProvider;
use Oro\Bundle\FormBundle\Model\UpdateHandlerFacade;
use Oro\Bundle\LayoutBundle\Attribute\Layout;
use Oro\Bundle\SecurityBundle\Attribute\Acl;
use Oro\Bundle\SecurityBundle\Attribute\CsrfProtection;
use Oro\Bundle\UIBundle\Route\Router;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Frontend controller for payment profile management
 */
class PaymentProfileController extends AbstractController
{
    /**
     * @return array
     */
    #[Route(path: '/', name: 'oro_authorize_net_payment_profile_frontend_index')]
    #[Layout(vars: ['entity_class', 'eCheckEnabled'])]
    #[Acl(
        id: 'oro_authorize_net_payment_profile_frontend_view',
        type: 'entity',
        class: CustomerPaymentProfile::class,
        permission: 'VIEW',
        groupName: 'commerce'
    )]
    public function indexAction()
    {
        $this->assertCIMEnabled();
        $config = $this->getCimEnabledConfig();

        $customerProfile = $this->container->get(CustomerProfileProvider::class)->findCustomerProfile();

        return [
            'entity_class' => CustomerPaymentProfile::class,
            'eCheckEnabled' => $config ? $config->isECheckEnabled() : false,
            'data' => [
                'customerProfile' => $customerProfile
            ]
        ];
    }

    /**
     *
     * @param string $type
     * @param Request $request
     * @return array
     */
    #[Route(
        path: '/create/{type}',
        name: 'oro_authorize_net_payment_profile_frontend_create',
        requirements: ['type' => 'creditcard|echeck']
    )]
    #[Layout]
    #[Acl(
        id: 'oro_authorize_net_payment_profile_frontend_create',
        type: 'entity',
        class: CustomerPaymentProfile::class,
        permission: 'CREATE',
        groupName: 'commerce'
    )]
    public function createAction($type, Request $request)
    {
        $this->assertCIMEnabled($type);

        $profile = new CustomerPaymentProfile($type);
        return $this->update(new PaymentProfileDTO($profile), $request);
    }

    /**
     *
     * @param CustomerPaymentProfile $paymentProfile
     * @param Request $request
     * @return array
     */
    #[Route(
        path: '/edit/{id}',
        name: 'oro_authorize_net_payment_profile_frontend_update',
        requirements: ['id' => '\d+']
    )]
    #[Layout]
    #[Acl(
        id: 'oro_authorize_net_payment_profile_frontend_update',
        type: 'entity',
        class: CustomerPaymentProfile::class,
        permission: 'EDIT',
        groupName: 'commerce'
    )]
    public function updateAction(CustomerPaymentProfile $paymentProfile, Request $request)
    {
        $this->assertCIMEnabled();

        return $this->update(new PaymentProfileDTO($paymentProfile), $request);
    }

    /**
     *
     * @param CustomerPaymentProfile $paymentProfile
     * @return JsonResponse
     */
    #[Route(
        path: '/delete/{id}',
        name: 'oro_authorize_net_payment_profile_frontend_delete',
        requirements: ['id' => '\d+'],
        methods: ['DELETE']
    )]
    #[Acl(
        id: 'oro_authorize_net_payment_profile_frontend_delete',
        type: 'entity',
        class: CustomerPaymentProfile::class,
        permission: 'DELETE',
        groupName: 'commerce'
    )]
    #[CsrfProtection()]
    public function deleteAction(CustomerPaymentProfile $paymentProfile)
    {
        $this->assertCIMEnabled();
        $successfull = true;

        $translator = $this->container->get(TranslatorInterface::class);
        $message = $translator->trans('oro.authorize_net.frontend.payment_profile.message.deleted');

        try {
            $this->container->get(CustomerPaymentProfileDeleteHandler::class)->handleDelete($paymentProfile);
        } catch (\Exception $exception) {//catch api error
            $successfull = false;
            $message = $translator->trans('oro.authorize_net.frontend.payment_profile.message.grid_not_deleted');
        }

        return new JsonResponse(['successful' => $successfull, 'message' => $message]);
    }

    /**
     *
     * @param CustomerProfile $customerProfile
     * @return JsonResponse
     */
    #[Route(
        path: '/delete-all/{id}',
        name: 'oro_authorize_net_payment_profile_frontend_delete_all',
        requirements: ['id' => '\d+'],
        methods: ['DELETE']
    )]
    #[Acl(
        id: 'oro_authorize_net_customer_profile_frontend_delete',
        type: 'entity',
        class: CustomerProfile::class,
        permission: 'DELETE',
        groupName: 'commerce'
    )]
    #[CsrfProtection()]
    public function deleteAllAction(CustomerProfile $customerProfile)
    {
        $this->assertCIMEnabled();
        $responseCode = JsonResponse::HTTP_NO_CONTENT;

        try {
            $this->container->get(CustomerProfileDeleteHandler::class)->handleDelete($customerProfile);
        } catch (\Exception $exception) {//catch api error
            $responseCode = JsonResponse::HTTP_INTERNAL_SERVER_ERROR;
        }

        return new JsonResponse(null, $responseCode);
    }

    /**
     * @return AuthorizeNetConfigInterface|null
     */
    private function getCimEnabledConfig()
    {
        $configProvider = $this->container->get(CIMEnabledIntegrationConfigProvider::class);

        return $configProvider->getConfig();
    }

    private function assertCIMEnabled(string $type = null)
    {
        $config = $this->getCimEnabledConfig();

        if (!$config) {
            throw $this->createAccessDeniedException();
        }

        if ($type === CustomerPaymentProfile::TYPE_ECHECK && !$config->isECheckEnabled()) {
            throw $this->createAccessDeniedException();
        }
    }

    /**
     * @param PaymentProfileDTO $paymentProfileDTO
     * @param Request $request
     * @return array|RedirectResponse
     */
    private function update(PaymentProfileDTO $paymentProfileDTO, Request $request)
    {
        $actionParams = ['route' => 'oro_authorize_net_payment_profile_frontend_index'];
        $request->attributes->set(Router::ACTION_PARAMETER, \json_encode($actionParams));

        $form = $this->container
            ->get(PaymentProfileDTOFormProvider::class)
            ->getPaymentProfileDTOForm($paymentProfileDTO);

        $formHandler = $this->container->get(PaymentProfileHandler::class);
        $updateHandler = $this->container->get(UpdateHandlerFacade::class);

        $result = $updateHandler->update(
            $paymentProfileDTO,
            $form,
            'oro.authorize_net.frontend.payment_profile.message.saved',
            $request,
            $formHandler
        );

        if ($result instanceof RedirectResponse) {
            return $result;
        }

        return [
            'data' => $result
        ];
    }

    #[\Override]
    public static function getSubscribedServices(): array
    {
        return [
            TranslatorInterface::class,
            CustomerProfileProvider::class,
            CustomerPaymentProfileDeleteHandler::class,
            CustomerProfileDeleteHandler::class,
            CIMEnabledIntegrationConfigProvider::class,
            PaymentProfileDTOFormProvider::class,
            PaymentProfileHandler::class,
            UpdateHandlerFacade::class,
        ];
    }
}
