<?php

namespace Oro\Bundle\AuthorizeNetBundle\Controller;

use Oro\Bundle\AuthorizeNetBundle\Service\AuthenticationCredentialsValidator;
use Oro\Bundle\AuthorizeNetBundle\Service\TransactionKeyValueProvider;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SecurityBundle\Annotation\CsrfProtection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Settings form controller to perform validation
 *
 * @Route(path="/settings")
 */
class SettingsController extends AbstractController
{
    /**
     * @Route(name="oro_authorize_net_settings_check_credentials", path="/check-credentials")
     * @AclAncestor("oro_authorize_net_settings_edit")
     * @Method("POST")
     * @CsrfProtection()
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function checkCredentialsAction(Request $request)
    {
        $transactionKey = $this
            ->get(TransactionKeyValueProvider::class)
            ->fromIntegrationEditFormValue(
                $request->get('integrationId'),
                $request->get('transactionKey')
            );

        $status = $this
            ->get(AuthenticationCredentialsValidator::class)
            ->isValid($request->get('apiLogin', ''), $transactionKey, $request->get('isTestMode') === '1');

        $message = $status ? 'credentials_are_valid' : 'credentials_are_not_valid';
        return new JsonResponse([
            'message' => $this->get(TranslatorInterface::class)
                ->trans('oro.authorize_net.settings.check_credentials.' . $message),
            'status'  => $status
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices()
    {
        return [
            TranslatorInterface::class,
            TransactionKeyValueProvider::class,
            AuthenticationCredentialsValidator::class,
        ];
    }
}
