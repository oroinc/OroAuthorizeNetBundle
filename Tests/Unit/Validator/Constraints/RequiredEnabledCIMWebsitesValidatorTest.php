<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\Validator\Constraints;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\AuthorizeNetBundle\Entity\AuthorizeNetSettings;
use Oro\Bundle\AuthorizeNetBundle\Validator\Constraints\RequiredEnabledCIMWebsites;
use Oro\Bundle\AuthorizeNetBundle\Validator\Constraints\RequiredEnabledCIMWebsitesValidator;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Component\Testing\ReflectionUtil;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class RequiredEnabledCIMWebsitesValidatorTest extends ConstraintValidatorTestCase
{
    /**
     * {@inheritDoc}
     */
    protected function createValidator(): RequiredEnabledCIMWebsitesValidator
    {
        return new RequiredEnabledCIMWebsitesValidator();
    }

    private function getAuthorizeNetSettings(int $id): AuthorizeNetSettings
    {
        $settings = new AuthorizeNetSettings();
        ReflectionUtil::setId($settings, $id);

        return $settings;
    }

    private function getChannel(int $id, bool $enabled): Channel
    {
        $channel = new Channel();
        ReflectionUtil::setId($channel, $id);
        $channel->setEnabled($enabled);

        return $channel;
    }

    private function getWebsite(int $id): Website
    {
        $website = new Website();
        ReflectionUtil::setId($website, $id);

        return $website;
    }

    public function testValidateNullValue()
    {
        $constraint = new RequiredEnabledCIMWebsites();
        $this->validator->validate(null, $constraint);

        $this->assertNoViolation();
    }

    public function testValidateForSettingsWithDisabledChannel()
    {
        $entity = $this->getAuthorizeNetSettings(111);
        $entity->setChannel($this->getChannel(1, false));
        $entity->setEnabledCIM(false);

        $constraint = new RequiredEnabledCIMWebsites();
        $this->validator->validate($entity, $constraint);

        $this->assertNoViolation();
    }

    public function testValidateForSettingsWithEnabledChannelButCIMFunctionalityIsDisabled()
    {
        $entity = $this->getAuthorizeNetSettings(111);
        $entity->setChannel($this->getChannel(1, true));
        $entity->setEnabledCIM(false);

        $constraint = new RequiredEnabledCIMWebsites();
        $this->validator->validate($entity, $constraint);

        $this->assertNoViolation();
    }

    public function testValidateForSettingsWithEnabledChannelAndCIMFunctionalityIsEnabledButNoEnabledCIMWebsites()
    {
        $entity = $this->getAuthorizeNetSettings(111);
        $entity->setChannel($this->getChannel(1, true));
        $entity->setEnabledCIM(true);

        $constraint = new RequiredEnabledCIMWebsites();
        $this->validator->validate($entity, $constraint);

        $this->buildViolation($constraint->message)
            ->atPath('property.path.enabledCIMWebsites')
            ->assertRaised();
    }

    public function testValidateForSettingsWithEnabledChannelAndCIMFunctionalityIsEnabledAndHaveEnabledCIMWebsites()
    {
        $entity = $this->getAuthorizeNetSettings(111);
        $entity->setChannel($this->getChannel(1, true));
        $entity->setEnabledCIM(true);
        $entity->setEnabledCIMWebsites(new ArrayCollection([$this->getWebsite(1)]));

        $constraint = new RequiredEnabledCIMWebsites();
        $this->validator->validate($entity, $constraint);

        $this->assertNoViolation();
    }
}
