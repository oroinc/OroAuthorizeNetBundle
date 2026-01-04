<?php

namespace Oro\Bundle\AuthorizeNetBundle\Layout\DataProvider;

use Oro\Bundle\AuthorizeNetBundle\Form\Type\PaymentProfileDTOType;
use Oro\Bundle\AuthorizeNetBundle\Model\DTO\PaymentProfileDTO;
use Oro\Bundle\AuthorizeNetBundle\Provider\CIMEnabledIntegrationConfigProvider;
use Oro\Bundle\LayoutBundle\Layout\DataProvider\AbstractFormProvider;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Layout data provider (create PaymentProfileDTOForm & form view)
 */
class PaymentProfileDTOFormProvider extends AbstractFormProvider
{
    public const PAYMENT_PROFILE_CREATE_ROUTE_NAME = 'oro_authorize_net_payment_profile_frontend_create';
    public const PAYMENT_PROFILE_UPDATE_ROUTE_NAME = 'oro_authorize_net_payment_profile_frontend_update';

    /** @var CIMEnabledIntegrationConfigProvider */
    private $configProvider;

    public function __construct(
        FormFactoryInterface $formFactory,
        UrlGeneratorInterface $router,
        CIMEnabledIntegrationConfigProvider $configProvider
    ) {
        parent::__construct($formFactory, $router);

        $this->configProvider = $configProvider;
    }

    /**
     * @param PaymentProfileDTO $paymentProfileDTO
     * @return FormView
     */
    public function getPaymentProfileDTOFormView(PaymentProfileDTO $paymentProfileDTO)
    {
        $options = $this->getFormOptions($paymentProfileDTO);

        return $this->getFormView(PaymentProfileDTOType::class, $paymentProfileDTO, $options);
    }

    /**
     * @param PaymentProfileDTO $paymentProfileDTO
     * @return FormInterface
     */
    public function getPaymentProfileDTOForm(PaymentProfileDTO $paymentProfileDTO)
    {
        $options = $this->getFormOptions($paymentProfileDTO);

        return $this->getForm(PaymentProfileDTOType::class, $paymentProfileDTO, $options);
    }

    /**
     * @return array
     */
    public function getPageComponentOptions()
    {
        $config = $this->configProvider->getConfig();

        return [
            'allowedCreditCards' => $config->getAllowedCreditCards(),
            'clientKey' => $config->getClientKey(),
            'apiLoginID' => $config->getApiLoginId(),
            'testMode' => $config->isTestMode(),
        ];
    }

    /**
     * @param PaymentProfileDTO $paymentProfileDTO
     * @return array
     */
    private function getFormOptions(PaymentProfileDTO $paymentProfileDTO)
    {
        $options = [];
        $paymentProfile = $paymentProfileDTO->getProfile();

        if ($paymentProfile->getId()) {
            $options['action'] = $this->generateUrl(
                self::PAYMENT_PROFILE_UPDATE_ROUTE_NAME,
                ['id' => $paymentProfile->getId()]
            );
        } else {
            $options['action'] = $this->generateUrl(
                self::PAYMENT_PROFILE_CREATE_ROUTE_NAME,
                ['type' => $paymentProfile->getType()]
            );
        }
        $config = $this->configProvider->getConfig();
        $options['requireCvvEntryEnabled'] = $config->isRequireCvvEntryEnabled();
        $options['allowed_account_types'] = $config->getECheckAccountTypes();
        $options['paymentProfileComponentOptions'] = $this->getPageComponentOptions();

        return $options;
    }
}
