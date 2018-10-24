<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\Validator\Constraints;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\AuthorizeNetBundle\Entity\AuthorizeNetSettings;
use Oro\Bundle\AuthorizeNetBundle\Form\Extension\EnabledCIMWebsitesSelectExtension;
use Oro\Bundle\AuthorizeNetBundle\Validator\Constraints\RequiredEnabledCIMWebsites;
use Oro\Bundle\AuthorizeNetBundle\Validator\Constraints\RequiredEnabledCIMWebsitesValidator;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Component\Testing\Unit\EntityTrait;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class RequiredEnabledCIMWebsitesValidatorTest extends \PHPUnit\Framework\TestCase
{
    use EntityTrait;

    /** @var RequiredEnabledCIMWebsites */
    private $constraints;

    /** @var RequiredEnabledCIMWebsitesValidator */
    private $validator;

    /** @var ExecutionContextInterface| \PHPUnit\Framework\MockObject\MockObject */
    private $context;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->constraints = new RequiredEnabledCIMWebsites();
        $this->validator = new RequiredEnabledCIMWebsitesValidator();
        $this->context = $this->createMock(ExecutionContextInterface::class);
        $this->validator->initialize($this->context);
    }

    /**
     * @dataProvider validateProvider
     *
     * @param bool       $expectedViolation
     * @param array|null $entityParams
     */
    public function testValidate($expectedViolation, array $entityParams = null)
    {
        $entity = null;
        if (null !== $entityParams) {
            $entity = $this->getEntity(
                AuthorizeNetSettings::class,
                array_merge($entityParams, ['id' => '111'])
            );
        }

        if (true === $expectedViolation) {
            $builder = $this->createMock(ConstraintViolationBuilderInterface::class);
            $builder->expects($this->once())
                ->method('atPath')
                ->with(EnabledCIMWebsitesSelectExtension::FIELD_NAME)
                ->willReturnSelf();
            $builder
                ->expects($this->once())
                ->method('addViolation');

            $this->context
                ->expects($this->once())
                ->method('buildViolation')
                ->with($this->constraints->message)
                ->willReturn($builder);
        } else {
            $this->context
                ->expects($this->never())
                ->method('buildViolation');
        }

        $this->validator->validate($entity, $this->constraints);
    }

    /**
     * @return array
     */
    public function validateProvider()
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
                'expectedViolation' => false,
                'entityParams' => null
            ],
            'Settings with disabled channel' => [
                'expectedViolation' => false,
                'entityParams' => [
                    'channel' => $disabledChannel,
                    'enabledCIM' => false
                ]
            ],
            'Settings with enabled channel, but CIM functionality is disabled' => [
                'expectedViolation' => false,
                'entityParams' => [
                    'channel' => $enabledChannel,
                    'enabledCIM' => false
                ]
            ],
            'Settings with enabled channel and CIM functionality is enabled' => [
                'expectedViolation' => true,
                'entityParams' => [
                    'channel' => $enabledChannel,
                    'enabledCIM' => true,
                    'enabledCIMWebsites' => new ArrayCollection([])
                ]
            ],
            'Valid settings' => [
                'expectedViolation' => false,
                'entityParams' => [
                    'channel' => $enabledChannel,
                    'enabledCIM' => true,
                    'enabledCIMWebsites' => new ArrayCollection([
                        $this->getEntity(Website::class, ['id' => 1])
                    ])
                ]
            ],
        ];
    }
}
