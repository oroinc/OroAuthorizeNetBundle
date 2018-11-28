<?php

namespace Oro\Bundle\AuthorizeNetBundle\Form\Type;

use Oro\Bundle\AddressBundle\Form\EventListener\AddressCountryAndRegionSubscriber;
use Oro\Bundle\AddressBundle\Form\Type\CountryType;
use Oro\Bundle\AddressBundle\Form\Type\RegionType;
use Oro\Bundle\AuthorizeNetBundle\Model\DTO\PaymentProfileAddressDTO;
use Oro\Bundle\FormBundle\Form\Extension\StripTagsExtension;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

/**
 * Type for address representation in payment profile
 */
class PaymentProfileAddressType extends AbstractType
{
    const NAME = 'oro_authorize_net_payment_profile_address';

    /** @var AddressCountryAndRegionSubscriber */
    protected $countryAndRegionSubscriber;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @param AddressCountryAndRegionSubscriber $eventListener
     * @param TranslatorInterface $translator
     */
    public function __construct(AddressCountryAndRegionSubscriber $eventListener, TranslatorInterface $translator)
    {
        $this->countryAndRegionSubscriber = $eventListener;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber($this->countryAndRegionSubscriber);

        $builder->add('firstName', TextType::class, [
            'required' => true,
            'label' => 'oro.authorize_net.frontend.payment_profile.address.first_name.label',
            StripTagsExtension::OPTION_NAME => true,
            'constraints' => [
                new NotBlank(),
                new Length(['max' => 50]),
                $this->createNonBracketsRegexConstraint()
            ]
        ])->add('lastName', TextType::class, [
            'required' => true,
            'label' => 'oro.authorize_net.frontend.payment_profile.address.last_name.label',
            StripTagsExtension::OPTION_NAME => true,
            'constraints' => [
                new NotBlank(),
                new Length(['max' => 50]),
                $this->createNonBracketsRegexConstraint()
            ]
        ])->add('company', TextType::class, [
            'required' => false,
            'label' => 'oro.authorize_net.frontend.payment_profile.address.company.label',
            StripTagsExtension::OPTION_NAME => true,
            'constraints' => [
                new Length(['max' => 50]),
                $this->createNonBracketsRegexConstraint()
            ]
        ])->add('street', TextType::class, [
            'required' => true,
            'label' => 'oro.authorize_net.frontend.payment_profile.address.street.label',
            StripTagsExtension::OPTION_NAME => true,
            'constraints' => [
                new NotBlank(),
                new Length(['max' => 60]),
                $this->createNonBracketsRegexConstraint()
            ]
        ])->add('country', CountryType::class, [
            'required' => true,
            'label' => 'oro.authorize_net.frontend.payment_profile.address.country.label',
            'constraints' => [
                new NotBlank()
            ]
        ])->add('city', TextType::class, [
            'required' => true,
            'label' => 'oro.address.city.label',
            StripTagsExtension::OPTION_NAME => true,
            'constraints' => [
                new NotBlank(),
                new Length(['max' => 40]),
                $this->createNonBracketsRegexConstraint()
            ]
        ])->add('region', RegionType::class, [
            'required' => true,
            'label' => 'oro.authorize_net.frontend.payment_profile.address.region.label'
        ])->add('region_text', HiddenType::class, [
            'required' => false,
            'random_id' => true
        ])->add('zip', TextType::class, [
            'required' => true,
            'label' => 'oro.authorize_net.frontend.payment_profile.address.zip.label',
            StripTagsExtension::OPTION_NAME => true,
            'constraints' => [
                new NotBlank(),
                new Length(['max' => 20]),
                $this->createNonBracketsRegexConstraint()
            ]
        ])->add('phoneNumber', TextType::class, [
            'required' => false,
            'label' => 'oro.authorize_net.frontend.payment_profile.address.phone_number.label',
            StripTagsExtension::OPTION_NAME => true,
            'constraints' => [
                new Length(['max' => 25])
            ]
        ])
        ->add('faxNumber', TextType::class, [
            'required' => false,
            'label' => 'oro.authorize_net.frontend.payment_profile.address.fax_number.label',
            StripTagsExtension::OPTION_NAME => true,
            'constraints' => [
                new Length(['max' => 25]),
                $this->createNonBracketsRegexConstraint()
            ]
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PaymentProfileAddressDTO::class,
            'region_route' => 'oro_api_frontend_country_get_regions'
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if (!empty($options['region_route'])) {
            $view->vars['region_route'] = $options['region_route'];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::NAME;
    }

    /**
     * This restriction is related to an error on the Authorize.Net API
     * "alert" and "()" in api request leads to malformed html resposne from api (HTTP 403)
     * @return Regex
     */
    private function createNonBracketsRegexConstraint()
    {
        return new Regex([
            'pattern' => '/^[^)(]+$/',
            'message' => $this->translator->trans('oro.authorize_net.validator.regex_non_brackets')
        ]);
    }
}
