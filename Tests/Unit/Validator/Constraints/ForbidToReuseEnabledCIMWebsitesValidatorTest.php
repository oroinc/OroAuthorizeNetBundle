<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\Validator\Constraints;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\AuthorizeNetBundle\Entity\AuthorizeNetSettings;
use Oro\Bundle\AuthorizeNetBundle\Entity\Repository\AuthorizeNetSettingsRepository;
use Oro\Bundle\AuthorizeNetBundle\Integration\AuthorizeNetChannelType;
use Oro\Bundle\AuthorizeNetBundle\Validator\Constraints\ForbidToReuseEnabledCIMWebsites;
use Oro\Bundle\AuthorizeNetBundle\Validator\Constraints\ForbidToReuseEnabledCIMWebsitesValidator;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Provider\WebsiteProviderInterface;
use Oro\Component\Testing\ReflectionUtil;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class ForbidToReuseEnabledCIMWebsitesValidatorTest extends ConstraintValidatorTestCase
{
    /** @var DoctrineHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $doctrineHelper;

    /** @var WebsiteProviderInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $websiteProvider;

    protected function setUp(): void
    {
        $this->doctrineHelper = $this->createMock(DoctrineHelper::class);
        $this->websiteProvider = $this->createMock(WebsiteProviderInterface::class);
        parent::setUp();
    }

    /**
     * {@inheritDoc}
     */
    protected function createValidator(): ForbidToReuseEnabledCIMWebsitesValidator
    {
        return new ForbidToReuseEnabledCIMWebsitesValidator($this->doctrineHelper, $this->websiteProvider);
    }

    private function getAuthorizeNetSettings(int $id): AuthorizeNetSettings
    {
        $settings = new AuthorizeNetSettings();
        ReflectionUtil::setId($settings, $id);

        return $settings;
    }

    private function getApplicableAuthorizeNetSetting(int $id): AuthorizeNetSettings
    {
        $settings = $this->getAuthorizeNetSettings($id);
        $settings->setChannel($this->getChannel(1, true));
        $settings->setEnabledCIM(true);
        $settings->setEnabledCIMWebsites(new ArrayCollection([$this->getWebsite(1, 'Default')]));

        return $settings;
    }

    private function getChannel(int $id, bool $enabled): Channel
    {
        $channel = new Channel();
        ReflectionUtil::setId($channel, $id);
        $channel->setEnabled($enabled);

        return $channel;
    }

    private function getWebsite(int $id, string $name): Website
    {
        $website = new Website();
        ReflectionUtil::setId($website, $id);
        $website->setName($name);

        return $website;
    }

    public function testValidateNullValue()
    {
        $constraint = new ForbidToReuseEnabledCIMWebsites();
        $this->validator->validate(null, $constraint);

        $this->assertNoViolation();
    }

    public function testValidateForSettingsWithDisabledChannel()
    {
        $entity = $this->getAuthorizeNetSettings(111);
        $entity->setChannel($this->getChannel(1, false));
        $entity->setEnabledCIM(false);

        $this->doctrineHelper->expects($this->never())
            ->method('getEntityRepository');

        $this->websiteProvider->expects($this->never())
            ->method('getWebsiteIds');

        $constraint = new ForbidToReuseEnabledCIMWebsites();
        $this->validator->validate($entity, $constraint);

        $this->assertNoViolation();
    }

    public function testValidateForSettingsWithEnabledChannelButCIMFunctionalityIsDisabled()
    {
        $entity = $this->getAuthorizeNetSettings(111);
        $entity->setChannel($this->getChannel(1, true));
        $entity->setEnabledCIM(false);

        $this->doctrineHelper->expects($this->never())
            ->method('getEntityRepository');

        $this->websiteProvider->expects($this->never())
            ->method('getWebsiteIds');

        $constraint = new ForbidToReuseEnabledCIMWebsites();
        $this->validator->validate($entity, $constraint);

        $this->assertNoViolation();
    }

    public function testValidateForSettingsWithEnabledChannelAndCIMFunctionalityIsEnabledButNoEnabledCIMWebsites()
    {
        $entity = $this->getAuthorizeNetSettings(111);
        $entity->setChannel($this->getChannel(1, true));
        $entity->setEnabledCIM(true);

        $this->doctrineHelper->expects($this->never())
            ->method('getEntityRepository');

        $this->websiteProvider->expects($this->never())
            ->method('getWebsiteIds');

        $constraint = new ForbidToReuseEnabledCIMWebsites();
        $this->validator->validate($entity, $constraint);

        $this->assertNoViolation();
    }

    public function testNoEnabledPaymentSettingValidate()
    {
        $entity = $this->getApplicableAuthorizeNetSetting(111);

        $repository = $this->createMock(AuthorizeNetSettingsRepository::class);
        $repository->expects($this->once())
            ->method('getEnabledSettingsWithCIMByTypeAndWebsites')
            ->with(
                AuthorizeNetChannelType::TYPE,
                $entity->getEnabledCIMWebsites()->toArray(),
                111
            )
            ->willReturn([]);

        $this->doctrineHelper->expects($this->once())
            ->method('getEntityRepository')
            ->willReturn($repository);

        $this->websiteProvider->expects($this->never())
            ->method('getWebsiteIds');

        $constraint = new ForbidToReuseEnabledCIMWebsites();
        $this->validator->validate($entity, $constraint);

        $this->assertNoViolation();
    }

    public function testInvalidSettingsWithOneWebsite()
    {
        $entity = $this->getApplicableAuthorizeNetSetting(111);

        $repository = $this->createMock(AuthorizeNetSettingsRepository::class);
        $repository->expects($this->once())
            ->method('getEnabledSettingsWithCIMByTypeAndWebsites')
            ->with(
                AuthorizeNetChannelType::TYPE,
                $entity->getEnabledCIMWebsites()->toArray(),
                111
            )
            ->willReturn($this->getApplicableAuthorizeNetSetting(112));

        $this->doctrineHelper->expects($this->once())
            ->method('getEntityRepository')
            ->willReturn($repository);

        $this->websiteProvider->expects($this->once())
            ->method('getWebsiteIds')
            ->willReturn([1]);

        $constraint = new ForbidToReuseEnabledCIMWebsites();
        $this->validator->validate($entity, $constraint);

        $this->buildViolation($constraint->messageSingleWebsite)
            ->atPath('property.path.enabledCIMWebsites')
            ->assertRaised();
    }

    public function testInvalidSettingsWithMultiWebsite()
    {
        $entity = $this->getApplicableAuthorizeNetSetting(111);

        $repository = $this->createMock(AuthorizeNetSettingsRepository::class);
        $repository->expects($this->once())
            ->method('getEnabledSettingsWithCIMByTypeAndWebsites')
            ->with(
                AuthorizeNetChannelType::TYPE,
                $entity->getEnabledCIMWebsites()->toArray(),
                111
            )
            ->willReturn([$this->getApplicableAuthorizeNetSetting(112)]);

        $this->doctrineHelper->expects($this->once())
            ->method('getEntityRepository')
            ->willReturn($repository);

        $this->websiteProvider->expects($this->once())
            ->method('getWebsiteIds')
            ->willReturn([1, 2]);

        $constraint = new ForbidToReuseEnabledCIMWebsites();
        $this->validator->validate($entity, $constraint);

        $this->buildViolation($constraint->messageMultiWebsite)
            ->setParameter('{{ websites }}', '"Default"')
            ->atPath('property.path.enabledCIMWebsites')
            ->assertRaised();
    }
}
