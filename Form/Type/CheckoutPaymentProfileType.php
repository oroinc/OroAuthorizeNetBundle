<?php

namespace Oro\Bundle\AuthorizeNetBundle\Form\Type;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\AuthorizeNetBundle\Entity\CustomerPaymentProfile;
use Oro\Bundle\AuthorizeNetBundle\Entity\CustomerProfile;
use Oro\Bundle\AuthorizeNetBundle\Provider\CustomerProfileProvider;
use Oro\Bundle\AuthorizeNetBundle\Provider\PaymentProfileProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Form type with payment profile selector & save profile checkbox
 */
class CheckoutPaymentProfileType extends AbstractType
{
    const NAME = 'oro_authorize_net_checkout_payment_profile';

    /** @var CustomerProfileProvider */
    private $customerProfileProvider;

    /** @var PaymentProfileProvider */
    private $paymentProfileProvider;

    /** @var TranslatorInterface */
    private $translator;

    public function __construct(
        CustomerProfileProvider $customerProfileProvider,
        PaymentProfileProvider $paymentProfileProvider,
        TranslatorInterface $translator
    ) {
        $this->customerProfileProvider = $customerProfileProvider;
        $this->paymentProfileProvider = $paymentProfileProvider;
        $this->translator = $translator;
    }

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $profileType = $options['profile_type'];
        $customerProfile = $this->customerProfileProvider->findCustomerProfile();
        $paymentProfiles = $customerProfile ?
            $this->getAllowedPaymentProfiles($customerProfile, $profileType)->toArray() :
            [];

        $defaultPaymentProfile = $this->getDefaultPaymentProfile($paymentProfiles);

        $builder->add('profile', ChoiceType::class, [
            'label' => 'oro.authorize_net.settings.form.groups.profile',
            'choice_label' => function (?CustomerPaymentProfile $profile = null) use ($profileType) {
                $label = $this->translator->trans(
                    sprintf(
                        'oro.authorize_net.frontend.payment_profile.checkout.new_%s_choice.label',
                        $profileType
                    )
                );
                if ($profile) {
                    $label = $this->translator->trans(
                        'oro.authorize_net.frontend.payment_profile.checkout.profile_choice.label',
                        [
                            '%name%' => $profile->getName(),
                            '%lastDigits%' => $profile->getLastDigits()
                        ]
                    );
                }

                return $label;
            },
            'choice_value' => function (?CustomerPaymentProfile $profile = null) {
                return $profile ? $profile->getId() : '';
            },
            'choices' => array_merge($paymentProfiles, [null]),
            'data' => $defaultPaymentProfile,
            'attr' => [
                'data-profile-selector' => true
            ]
        ]);

        $builder->add('saveProfile', CheckoutSaveProfileType::class, [
            'label' => 'oro.authorize_net.frontend.payment_profile.checkout.save_profile.label',
            'tooltip' => 'oro.authorize_net.frontend.payment_profile.checkout.save_profile.tooltip',
            'block_name' => 'save_profile',
            'attr' => [
                'data-save-profile' => true
            ]
        ]);
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'label' => false,
            'csrf_protection' => false,
            'profile_type' => CustomerPaymentProfile::TYPE_CREDITCARD
        ]);
        $resolver->setAllowedValues('profile_type', CustomerPaymentProfile::ALLOWED_TYPES);
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return self::NAME;
    }

    /**
     * @param CustomerPaymentProfile[] $paymentProfiles
     * @return CustomerPaymentProfile|null
     */
    private function getDefaultPaymentProfile(array $paymentProfiles)
    {
        $filteredProfiles = \array_filter($paymentProfiles, function (CustomerPaymentProfile $paymentProfile) {
            return $paymentProfile->isDefault();
        });

        $defaultPaymentProfile = $filteredProfiles ? reset($filteredProfiles) : reset($paymentProfiles);

        if (!$defaultPaymentProfile) {
            $defaultPaymentProfile = null;
        }

        return $defaultPaymentProfile;
    }

    /**
     * @param CustomerProfile $customerProfile
     * @param string $profileType
     * @return ArrayCollection|CustomerPaymentProfile[]
     */
    private function getAllowedPaymentProfiles(CustomerProfile $customerProfile, string $profileType)
    {
        $allowedExternalIds = $this->paymentProfileProvider->getPaymentProfileExternalIds($customerProfile);
        $paymentProfiles = $customerProfile->getPaymentProfilesByType($profileType)->filter(
            function (CustomerPaymentProfile $paymentProfile) use ($allowedExternalIds) {
                $externalId = $paymentProfile->getCustomerPaymentProfileId();

                return \in_array($externalId, $allowedExternalIds, true);
            }
        );

        return $paymentProfiles;
    }
}
