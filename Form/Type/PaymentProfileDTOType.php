<?php

namespace Oro\Bundle\AuthorizeNetBundle\Form\Type;

use Oro\Bundle\AuthorizeNetBundle\Entity\AuthorizeNetSettings;
use Oro\Bundle\AuthorizeNetBundle\Entity\CustomerPaymentProfile;
use Oro\Bundle\AuthorizeNetBundle\Model\DTO\PaymentProfileDTO;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Payment profile form with related data (full)
 */
class PaymentProfileDTOType extends AbstractType
{
    const NAME = 'oro_authorize_net_payment_profile_dto';

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('profile', PaymentProfileType::class, [
            'label' => false,
            'required' => true,
        ]);
        $builder->add('address', PaymentProfileAddressType::class);
        $builder->add('encodedData', PaymentProfileEncodedDataType::class, [
            'label' => false
        ]);
        $builder->add('maskedData', PaymentProfileMaskedDataType::class, [
            'label' => false
        ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
            /** @var PaymentProfileDTO $dto */
            $dto = $event->getData();
            $form = $event->getForm();
            if ($dto === null) {
                return;
            }

            $profileType = $dto->getProfile()->getType();

            $form->add('updatePaymentData', CheckboxType::class, [
                'label' => sprintf(
                    'oro.authorize_net.frontend.payment_profile.form.fields.update_%s.label',
                    $profileType
                )
            ]);

            if ($profileType === CustomerPaymentProfile::TYPE_CREDITCARD) {
                $form->add('paymentData', CreditCardType::class, [
                    'disabled' => true,
                    'mapped' => false,
                    'requireCvvEntryEnabled' => $options['requireCvvEntryEnabled'],
                    'allowedCreditCards' => $options['paymentProfileComponentOptions']['allowedCreditCards'] ?? []
                ]);
            }

            if ($profileType === CustomerPaymentProfile::TYPE_ECHECK) {
                $form->add('paymentData', BankAccountType::class, [
                    'disabled' => true,
                    'mapped' => false,
                    'allowed_account_types' => $options['allowed_account_types']
                ]);
            }
        });
    }

    #[\Override]
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['paymentProfileComponentOptions'] = $options['paymentProfileComponentOptions'];
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PaymentProfileDTO::class,
            'requireCvvEntryEnabled' => true,
            'allowed_account_types' => AuthorizeNetSettings::ECHECK_ACCOUNT_TYPES,
            'paymentProfileComponentOptions' => [],
            'attr' => [
                'data-payment-profile-form' => true
            ]
        ]);
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return self::NAME;
    }
}
