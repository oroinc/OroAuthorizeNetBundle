<?php

namespace Oro\Bundle\AuthorizeNetBundle\Controller;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Seetings form controller to perform validation
 * @Route(path="/settings")
 */
class SettingsController extends Controller
{
    /**
     * @Route(name="oro_authorize_net_settings_check_credentials", path="/check-credentials")
     * @AclAncestor("oro_authorize_net_settings_edit")
     */
    public function checkCredentialsAction(Request $request)
    {
        $transactionKey = $this
            ->get('oro_authorize_net.service.transaction_key_value_provider')
            ->fromIntegrationEditFormValue(
                $request->get('integrationId'),
                $request->get('transactionKey')
            );

        $status = $this
            ->get('oro_authorize_net.service.authentication_credentials_validator_service')
            ->isValid($request->get('apiLogin', ''), $transactionKey, $request->get('isTestMode') === '1');

        $message = $status ? 'credentials_are_valid' : 'credentials_are_not_valid';
        return new JsonResponse([
            'message' => $this->get('translator')->trans('oro.authorize_net.settings.check_credentials.' . $message),
            'status'  => $status
        ]);
    }
}
