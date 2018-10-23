<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\Validator\Constraints;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\AuthorizeNetBundle\Entity\AuthorizeNetSettings;
use Oro\Bundle\AuthorizeNetBundle\Entity\Repository\AuthorizeNetSettingsRepository;
use Oro\Bundle\AuthorizeNetBundle\Form\Extension\EnabledCIMWebsitesSelectExtension;
use Oro\Bundle\AuthorizeNetBundle\Integration\AuthorizeNetChannelType;
use Oro\Bundle\AuthorizeNetBundle\Validator\Constraints\ForbidToReuseEnabledCIMWebsites;
use Oro\Bundle\AuthorizeNetBundle\Validator\Constraints\ForbidToReuseEnabledCIMWebsitesValidator;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Provider\WebsiteProviderInterface;
use Oro\Component\Testing\Unit\EntityTrait;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class ForbidToReuseEnabledCIMWebsitesValidatorTest extends \PHPUnit\Framework\TestCase
{
    use EntityTrait;

    /** @var ForbidToReuseEnabledCIMWebsites */
    private $constraints;

    /** @var ForbidToReuseEnabledCIMWebsitesValidator */
    private $validator;

    /** @var ExecutionContextInterface| \PHPUnit\Framework\MockObject\MockObject */
    private $context;

    /** @var WebsiteProviderInterface| \PHPUnit\Framework\MockObject\MockObject */
    private $websiteProvider;

    /** @var DoctrineHelper| \PHPUnit\Framework\MockObject\MockObject */
    private $doctrineHelper;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->websiteProvider = $this->createMock(WebsiteProviderInterface::class);
        $this->doctrineHelper = $this->createMock(DoctrineHelper::class);
        $this->constraints = new ForbidToReuseEnabledCIMWebsites();
        $this->validator = new ForbidToReuseEnabledCIMWebsitesValidator($this->doctrineHelper, $this->websiteProvider);
        $this->context = $this->createMock(ExecutionContextInterface::class);
        $this->validator->initialize($this->context);
    }

    /**
     * @dataProvider validateWithNoApplicableValueProvider
     *
     * @param array|null $entityParams
     */
    public function testValidateWithNoApplicableValue(array $entityParams = null)
    {
        $entity = null;
        if (null !== $entityParams) {
            $entity = $this->getEntity(
                AuthorizeNetSettings::class,
                array_merge($entityParams, ['id' => '111'])
            );
        }

        $this->doctrineHelper
            ->expects($this->never())
            ->method('getEntityRepository');

        $this->websiteProvider
            ->expects($this->never())
            ->method('getWebsiteIds');

        $this->validator->validate($entity, $this->constraints);
    }

    /**
     * @return array
     */
    public function validateWithNoApplicableValueProvider()
    {
        $enabledChannel = $this->getEntity(Channel::class, [
            'id' => 1,
            'enabled' => true
        ]);

        $disabledChannel = $this->getEntity(Channel::class, [
            'id' => 2,
            'enabled' => false
        ]);

        return [
            'Null value' => [
                'entityParams' => null
            ],
            'Settings with disabled channel' => [
                'entityParams' => [
                    'channel' => $disabledChannel,
                    'enabledCIM' => false
                ]
            ],
            'Settings with enabled channel, but CIM functionality is disabled' => [
                'entityParams' => [
                    'channel' => $enabledChannel,
                    'enabledCIM' => false
                ]
            ],
            'Settings with enabled channel and CIM functionality is enabled, but without enabled websites' => [
                'entityParams' => [
                    'channel' => $enabledChannel,
                    'enabledCIM' => true,
                    'enabledCIMWebsites' => new ArrayCollection([])
                ]
            ]
        ];
    }

    public function testNoEnabledPaymentSettingValidate()
    {
        $entity = $this->getApplicableAuthorizeNetSetting(111);

        $repository = $this->createMock(AuthorizeNetSettingsRepository::class);
        $repository
            ->expects($this->once())
            ->method('getEnabledSettingsWithCIMByTypeAndWebsites')
            ->with(
                AuthorizeNetChannelType::TYPE,
                $entity->getEnabledCIMWebsites()->toArray(),
                111
            )
            ->willReturn([]);

        $this->doctrineHelper
            ->expects($this->once())
            ->method('getEntityRepository')
            ->willReturn($repository);

        $this->websiteProvider
            ->expects($this->never())
            ->method('getWebsiteIds');

        $this->validator->validate($entity, $this->constraints);
    }

    public function testInvalidSettingsWithOneWebsite()
    {
        $entity = $this->getApplicableAuthorizeNetSetting(111);

        $repository = $this->createMock(AuthorizeNetSettingsRepository::class);
        $repository
            ->expects($this->once())
            ->method('getEnabledSettingsWithCIMByTypeAndWebsites')
            ->with(
                AuthorizeNetChannelType::TYPE,
                $entity->getEnabledCIMWebsites()->toArray(),
                111
            )
            ->willReturn($this->getApplicableAuthorizeNetSetting(112));

        $this->doctrineHelper
            ->expects($this->once())
            ->method('getEntityRepository')
            ->willReturn($repository);

        $this->websiteProvider
            ->expects($this->once())
            ->method('getWebsiteIds')
            ->willReturn([1]);

        $this->assertViolationWithMessage($this->constraints->messageSingleWebsite);

        $this->validator->validate($entity, $this->constraints);
    }

    public function testInvalidSettingsWithMultiWebsite()
    {
        $entity = $this->getApplicableAuthorizeNetSetting(111);

        $repository = $this->createMock(AuthorizeNetSettingsRepository::class);
        $repository
            ->expects($this->once())
            ->method('getEnabledSettingsWithCIMByTypeAndWebsites')
            ->with(
                AuthorizeNetChannelType::TYPE,
                $entity->getEnabledCIMWebsites()->toArray(),
                111
            )
            ->willReturn([$this->getApplicableAuthorizeNetSetting(112)]);

        $this->doctrineHelper
            ->expects($this->once())
            ->method('getEntityRepository')
            ->willReturn($repository);

        $this->websiteProvider
            ->expects($this->once())
            ->method('getWebsiteIds')
            ->willReturn([1, 2]);

        $this->assertViolationWithMessage(
            $this->constraints->messageMultiWebsite,
            '"Default"'
        );

        $this->validator->validate($entity, $this->constraints);
    }

    /**
     * @param string      $message
     * @param null|string $messageParameter
     */
    private function assertViolationWithMessage($message, $messageParameter = null)
    {
        $builder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $builder->expects($this->once())
            ->method('atPath')
            ->with(EnabledCIMWebsitesSelectExtension::FIELD_NAME)
            ->willReturnSelf();

        $builder
            ->expects($this->once())
            ->method('addViolation');

        if (null !== $messageParameter) {
            $builder
                ->expects($this->once())
                ->method('setParameter')
                ->with('{{ websites }}', $messageParameter)
                ->willReturnSelf();
        }

        $this->context
            ->expects($this->once())
            ->method('buildViolation')
            ->with($message)
            ->willReturn($builder);
    }

    /**
     * @param int $id
     * @return AuthorizeNetSettings
     */
    private function getApplicableAuthorizeNetSetting($id)
    {
        /** @var AuthorizeNetSettings $entity */
        $entity = $this->getEntity(
            AuthorizeNetSettings::class,
            [
                'id' => $id,
                'channel' => $this->getEntity(Channel::class, [
                    'id' => 1,
                    'enabled' => true
                ]),
                'enabledCIM' => true,
                'enabledCIMWebsites' => new ArrayCollection([
                    $this->getEntity(Website::class, ['id' => 1, 'name' => 'Default'])
                ])
            ]
        );

        return $entity;
    }
}
