<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\Acl\Voter;

use Doctrine\ORM\EntityRepository;
use Oro\Bundle\AuthorizeNetBundle\Acl\Voter\CustomerProfileAndCustomerPaymentProfileVoter;
use Oro\Bundle\AuthorizeNetBundle\Entity\CustomerPaymentProfile;
use Oro\Bundle\AuthorizeNetBundle\Entity\CustomerProfile;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessor;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Component\Testing\Unit\EntityTrait;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class CustomerProfileAndPaymentProfileVoterTest extends \PHPUnit\Framework\TestCase
{
    use EntityTrait;

    /** @var DoctrineHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $doctrineHelper;

    /** @var TokenAccessor|\PHPUnit\Framework\MockObject\MockObject */
    private $tokenAccessor;

    /** @var CustomerProfileAndCustomerPaymentProfileVoter */
    private $voter;

    /** @var TokenInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $token;

    protected function setUp()
    {
        $this->doctrineHelper = $this->createMock(DoctrineHelper::class);
        $this->tokenAccessor = $this->createMock(TokenAccessor::class);
        $this->token = $this->createMock(TokenInterface::class);

        $this->voter = new CustomerProfileAndCustomerPaymentProfileVoter($this->doctrineHelper, $this->tokenAccessor);
    }

    /**
     * @dataProvider voteDataProvider
     * @param CustomerPaymentProfile|CustomerProfile $object
     * @param CustomerUser $objectCustomerUser
     * @param CustomerUser|User|null $tokenUser
     * @param int $expectedResult
     */
    public function testVote($object, CustomerUser $objectCustomerUser, $tokenUser, $expectedResult)
    {
        $objectClass = \get_class($object);
        $this->voter->setClassName($objectClass);

        $object->setCustomerUser($objectCustomerUser);

        $this->token
            ->expects($this->once())
            ->method('getUser')
            ->willReturn(new CustomerUser());

        $this->tokenAccessor
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($tokenUser);

        $this->doctrineHelper->expects($this->once())
            ->method('getSingleEntityIdentifier')
            ->with($object, false)
            ->willReturn(1);

        $repository = $this->createMock(EntityRepository::class);

        $repository->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($object);

        $this->doctrineHelper->expects($this->once())
            ->method('getEntityRepository')
            ->with($objectClass)
            ->willReturn($repository);

        $this->assertEquals($expectedResult, $this->voter->vote($this->token, $object, ['ANY']));
    }

    public function testVoteWithNonCustomerUser()
    {
        $this->token
            ->expects($this->once())
            ->method('getUser')
            ->willReturn(new User());

        $actualResult = $this->voter->vote($this->token, new CustomerPaymentProfile(), ['ANY']);
        $this->assertEquals(CustomerProfileAndCustomerPaymentProfileVoter::ACCESS_ABSTAIN, $actualResult);
    }

    public function voteDataProvider()
    {
        $customerUser1 = new CustomerUser();
        $customerUser2 = new CustomerUser();
        $customerPaymentProfile = new CustomerPaymentProfile();
        $customerProfile = new CustomerProfile();

        return [
            'payment profile granted' => [
                'object' => $customerPaymentProfile,
                'objectCustomerUser' => $customerUser1,
                'tokenUser' => $customerUser1,
                'expectedResult' => CustomerProfileAndCustomerPaymentProfileVoter::ACCESS_GRANTED
            ],
            'payment profile denied' => [
                'object' => $customerPaymentProfile,
                'objectCustomerUser' => $customerUser1,
                'tokenUser' => $customerUser2,
                'expectedResult' => CustomerProfileAndCustomerPaymentProfileVoter::ACCESS_DENIED
            ],
            'payment profile denied (token accessor user == null)' => [
                'object' => $customerPaymentProfile,
                'objectCustomerUser' => $customerUser1,
                'tokenUser' => null,
                'expectedResult' => CustomerProfileAndCustomerPaymentProfileVoter::ACCESS_DENIED
            ],
            'customer profile granted' => [
                'object' => $customerProfile,
                'objectCustomerUser' => $customerUser1,
                'tokenUser' => $customerUser1,
                'expectedResult' => CustomerProfileAndCustomerPaymentProfileVoter::ACCESS_GRANTED
            ],
            'customer profile denied' => [
                'object' => $customerProfile,
                'objectCustomerUser' => $customerUser1,
                'tokenUser' => $customerUser2,
                'expectedResult' => CustomerProfileAndCustomerPaymentProfileVoter::ACCESS_DENIED
            ],
            'customer profile denied (token accessor user == null)' => [
                'object' => $customerProfile,
                'objectCustomerUser' => $customerUser1,
                'tokenUser' => null,
                'expectedResult' => CustomerProfileAndCustomerPaymentProfileVoter::ACCESS_DENIED
            ]
        ];
    }
}
