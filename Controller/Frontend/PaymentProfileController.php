<?php

namespace Oro\Bundle\AuthorizeNetBundle\Controller\Frontend;

use Oro\Bundle\AuthorizeNetBundle\Entity\CustomerPaymentProfile;
use Oro\Bundle\AuthorizeNetBundle\Entity\CustomerProfile;
use Oro\Bundle\AuthorizeNetBundle\Method\Config\AuthorizeNetConfigInterface;
use Oro\Bundle\AuthorizeNetBundle\Model\DTO\PaymentProfileDTO;
use Oro\Bundle\LayoutBundle\Annotation\Layout;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\UIBundle\Route\Router;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Frontend controller for payment profile management
 */
class PaymentProfileController extends Controller
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

        $customerProfile = $this->get('oro_authorize_net.provider.customer_profile')->findCustomerProfile();

        return [
            'entity_class' => $this->getParameter('oro_authorize_net.entity.customer_payment_profile.class'),
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
        $message = $this->get('translator')->trans('oro.authorize_net.frontend.payment_profile.message.deleted');
        try {
            $this->get('oro_authorize_net.handler.delete.customer_payment_profile')->handleDelete($paymentProfile);
        } catch (\Exception $exception) {//catch api error
            $successfull = false;
            $message = $this->get('translator')
                ->trans('oro.authorize_net.frontend.payment_profile.message.grid_not_deleted');
        }

        return new JsonResponse(['successful' => $successfull, 'message' => $message]);
    }

    /**
     * @Route("/delete-all/{id}", name="oro_authorize_net_payment_profile_frontend_delete_all",requirements={"id"="\d+"})
     * @Method("DELETE")
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
            $this->get('oro_authorize_net.handler.delete.customer_profile')->handleDelete($customerProfile);
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
        $configProvider = $this->get('oro_authorize_net.provider.cim_enabled_integration_config');

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
            ->get('oro_authorize_net.layout.data_provider.payment_profile_dto_form')
            ->getPaymentProfileDTOForm($paymentProfileDTO);

        $formHandler = $this->get('oro_authorize_net.form.handler.payment_profile');
        $updateHandler = $this->get('oro_form.update_handler');

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
}
