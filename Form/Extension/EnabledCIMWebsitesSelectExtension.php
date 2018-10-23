<?php

namespace Oro\Bundle\AuthorizeNetBundle\Form\Extension;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\AuthorizeNetBundle\Form\Type\AuthorizeNetSettingsType;
use Oro\Bundle\AuthorizeNetBundle\Validator\Constraints\ForbidToReuseEnabledCIMWebsites;
use Oro\Bundle\AuthorizeNetBundle\Validator\Constraints\RequiredEnabledCIMWebsites;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Oro\Bundle\WebsiteBundle\Provider\WebsiteProviderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Use to add field that manage enabledCIMWebsitesSelect
 */
class EnabledCIMWebsitesSelectExtension extends AbstractTypeExtension
{
    const FIELD_NAME = 'enabledCIMWebsites';

    /** @var WebsiteProviderInterface */
    private $websiteProvider;

    /** @var WebsiteManager */
    private $websiteManager;

    /**
     * @param WebsiteProviderInterface $websiteProvider
     * @param WebsiteManager $websiteManager
     */
    public function __construct(WebsiteProviderInterface $websiteProvider, WebsiteManager $websiteManager)
    {
        $this->websiteProvider = $websiteProvider;
        $this->websiteManager = $websiteManager;
    }

    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $fieldOptions = [
            'class' => Website::class,
            'label' => 'oro.authorize_net.settings.enabled_cim_websites.label',
            'tooltip' => 'oro.authorize_net.settings.enabled_cim_websites.tooltip',
            'multiple' => true,
            'required' => false,
        ];

        /**
         * The minimum count of website registered in application is one
         */
        if (1 === count($this->websiteProvider->getWebsiteIds())) {
            $fieldOptions['attr']['readonly'] = true;
            $fieldOptions['data'] = new ArrayCollection([
                $this->websiteManager->getDefaultWebsite()
            ]);
        }

        $builder->add(
            self::FIELD_NAME,
            EntityType::class,
            $fieldOptions
        );
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'constraints' => [
                new RequiredEnabledCIMWebsites(),
                new ForbidToReuseEnabledCIMWebsites()
            ]
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function getExtendedType()
    {
        return AuthorizeNetSettingsType::class;
    }
}
