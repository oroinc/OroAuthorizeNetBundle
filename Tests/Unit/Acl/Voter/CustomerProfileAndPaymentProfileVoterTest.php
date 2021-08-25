<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\Acl\Voter;

use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\AuthorizeNetBundle\Acl\Voter\CustomerProfileAndCustomerPaymentProfileVoter;
use Oro\Bundle\AuthorizeNetBundle\Entity\CustomerPaymentProfile;
use Oro\Bundle\AuthorizeNetBundle\Entity\CustomerProfile;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class CustomerProfileAndPaymentProfileVoterTest extends \PHPUnit\Framework\TestCase
{
    /** @var DoctrineHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $doctrineHelper;

    /** @var TokenInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $token;

    protected function setUp(): void
    {
        $this->doctrineHelper = $this->createMock(DoctrineHelper::class);
        $this->token = $this->createMock(TokenInterface::class);
    }

    private function getVoter(string $className): CustomerProfileAndCustomerPaymentProfileVoter
    {
        return new CustomerProfileAndCustomerPaymentProfileVoter($this->doctrineHelper, $className);
    }

    public function testVoteWithoutUser()
    {
        $this->token->expects($this->once())
            ->method('getUser')
            ->willReturn(null);

        $voter = $this->getVoter(CustomerPaymentProfile::class);
        $this->assertSame(
            VoterInterface::ACCESS_DENIED,
            $voter->vote($this->token, new CustomerPaymentProfile(), ['ANY'])
        );
    }

    public function testVoteWithNonCustomerUser()
    {
        $this->token->expects($this->once())
            ->method('getUser')
            ->willReturn(new User());

        $voter = $this->getVoter(CustomerPaymentProfile::class);
        $this->assertSame(
            VoterInterface::ACCESS_ABSTAIN,
            $voter->vote($this->token, new CustomerPaymentProfile(), ['ANY'])
        );
    }

    /**
     * @dataProvider voteDataProvider
     */
    public function testVote(
        CustomerPaymentProfile|CustomerProfile $object,
        CustomerUser $objectCustomerUser,
        CustomerUser $tokenUser,
        int $expectedResult
    ) {
        $objectClass = get_class($object);
        $objectIdentifier = 1;

        $object->setCustomerUser($objectCustomerUser);

        $this->token->expects($this->once())
            ->method('getUser')
            ->willReturn($tokenUser);

        $this->doctrineHelper->expects($this->once())
            ->method('getSingleEntityIdentifier')
            ->with($object, false)
            ->willReturn($objectIdentifier);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())
            ->method('find')
            ->with($objectClass, $objectIdentifier)
            ->willReturn($object);

        $this->doctrineHelper->expects($this->once())
            ->method('getEntityManagerForClass')
            ->with($objectClass)
            ->willReturn($em);

        $voter = $this->getVoter($objectClass);
        $this->assertEquals($expectedResult, $voter->vote($this->token, $object, ['ANY']));
    }

    public function voteDataProvider(): array
    {
        $customerUser = new CustomerUser();

        return [
            'payment profile granted' => [
                'object' => new CustomerPaymentProfile(),
                'objectCustomerUser' => $customerUser,
                'tokenUser' => $customerUser,
                'expectedResult' => VoterInterface::ACCESS_GRANTED
            ],
            'payment profile denied' => [
                'object' => new CustomerPaymentProfile(),
                'objectCustomerUser' => $customerUser,
                'tokenUser' => new CustomerUser(),
                'expectedResult' => VoterInterface::ACCESS_DENIED
            ],
            'customer profile granted' => [
                'object' => new CustomerProfile(),
                'objectCustomerUser' => $customerUser,
                'tokenUser' => $customerUser,
                'expectedResult' => VoterInterface::ACCESS_GRANTED
            ],
            'customer profile denied' => [
                'object' => new CustomerProfile(),
                'objectCustomerUser' => $customerUser,
                'tokenUser' => new CustomerUser(),
                'expectedResult' => VoterInterface::ACCESS_DENIED
            ]
        ];
    }
}
