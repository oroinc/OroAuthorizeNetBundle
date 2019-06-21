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
use Oro\Bundle\LayoutBundle\Annotation\Layout;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\CsrfProtection;
use Oro\Bundle\UIBundle\Route\Router;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Frontend controller for payment profile management
 */
class PaymentProfileController extends AbstractController
{
    /**
     * @Route("/", name="oro_authorize_net_payment_profile_frontend_index")
     * @Layout(vars={"entity_class", "eCheckEnabled"})
     * @Acl(
     *      id="oro_authorize_net_payment_profile_frontend_view",
     *      type="entity",
     *      class="OroAuthorizeNetBundle:CustomerPaymentProfile",
     *      permission="VIEW",
     *      group_name="commerce"
     * )
     *
     * @return array
     */
    public function indexAction()
    {
        $this->assertCIMEnabled();
        $config = $this->getCimEnabledConfig();

        $customerProfile = $this->get(CustomerProfileProvider::class)->findCustomerProfile();

        return [
            'entity_class' => CustomerPaymentProfile::class,
            'eCheckEnabled' => $config ? $config->isECheckEnabled() : false,
            'data' => [
                'customerProfile' => $customerProfile
            ]
        ];
    }

    /**
     * @Route(
     *     "/create/{type}",
     *     name="oro_authorize_net_payment_profile_frontend_create",
     *     requirements={
     *         "type": "creditcard|echeck",
     *     }
     * )
     * @Layout()
     * @Acl(
     *      id="oro_authorize_net_payment_profile_frontend_create",
     *      type="entity",
     *      class="OroAuthorizeNetBundle:CustomerPaymentProfile",
     *      permission="CREATE",
     *      group_name="commerce"
     * )
     *
     * @param string $type
     * @param Request $request
     * @return array
     */
    public function createAction($type, Request $request)
    {
        $this->assertCIMEnabled($type);

        $profile = new CustomerPaymentProfile($type);
        return $this->update(new PaymentProfileDTO($profile), $request);
    }

    /**
     * @Route("/edit/{id}", name="oro_authorize_net_payment_profile_frontend_update", requirements={"id"="\d+"})
     * @Layout()
     * @Acl(
     *      id="oro_authorize_net_payment_profile_frontend_update",
     *      type="entity",
     *      class="OroAuthorizeNetBundle:CustomerPaymentProfile",
     *      permission="EDIT",
     *      group_name="commerce"
     * )
     *
     * @param CustomerPaymentProfile $paymentProfile
     * @param Request $request
     * @return array
     */
    public function updateAction(CustomerPaymentProfile $paymentProfile, Request $request)
    {
        $this->assertCIMEnabled();

        return $this->update(new PaymentProfileDTO($paymentProfile), $request);
    }

    /**
     * @Route("/delete/{id}", name="oro_authorize_net_payment_profile_frontend_delete", requirements={"id"="\d+"})
     * @Method("DELETE")
     * @CsrfProtection()
     * @Acl(
     *      id="oro_authorize_net_payment_profile_frontend_delete",
     *      type="entity",
     *      class="OroAuthorizeNetBundle:CustomerPaymentProfile",
     *      permission="DELETE",
     *      group_name="commerce"
     * )
     *
     * @param CustomerPaymentProfile $paymentProfile
     * @return JsonResponse
     */
    public function deleteAction(CustomerPaymentProfile $paymentProfile)
    {
        $this->assertCIMEnabled();
        $successfull = true;

        $translator = $this->get(TranslatorInterface::class);
        $message = $translator->trans('oro.authorize_net.frontend.payment_profile.message.deleted');

        try {
            $this->get(CustomerPaymentProfileDeleteHandler::class)->handleDelete($paymentProfile);
        } catch (\Exception $exception) {//catch api error
            $successfull = false;
            $message = $translator->trans('oro.authorize_net.frontend.payment_profile.message.grid_not_deleted');
        }

        return new JsonResponse(['successful' => $successfull, 'message' => $message]);
    }

    /**
     * @Route("/delete-all/{id}", name="oro_authorize_net_payment_profile_frontend_delete_all",requirements={"id"="\d+"})
     * @Method("DELETE")
     * @CsrfProtection()
     * @Acl(
     *      id="oro_authorize_net_customer_profile_frontend_delete",
     *      type="entity",
     *      class="OroAuthorizeNetBundle:CustomerProfile",
     *      permission="DELETE",
     *      group_name="commerce"
     * )
     *
     * @param CustomerProfile $customerProfile
     * @return JsonResponse
     */
    public function deleteAllAction(CustomerProfile $customerProfile)
    {
        $this->assertCIMEnabled();
        $responseCode = JsonResponse::HTTP_NO_CONTENT;

        try {
            $this->get(CustomerProfileDeleteHandler::class)->handleDelete($customerProfile);
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
        $configProvider = $this->get(CIMEnabledIntegrationConfigProvider::class);

        return $configProvider->getConfig();
    }

    /**
     * @param string|null $type
     */
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

        $form = $this
            ->get(PaymentProfileDTOFormProvider::class)
            ->getPaymentProfileDTOForm($paymentProfileDTO);

        $formHandler = $this->get(PaymentProfileHandler::class);
        $updateHandler = $this->get(UpdateHandlerFacade::class);

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

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices()
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
