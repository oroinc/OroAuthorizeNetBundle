<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\AuthorizeNetBundle\Entity\AuthorizeNetSettings;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadOrganization;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadOtherOrganizations;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadUser;
use Oro\Bundle\UserBundle\Migrations\Data\ORM\LoadAdminUserData;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class LoadAuthorizeNetChannelData extends AbstractFixture implements DependentFixtureInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * {@inheritDoc}
     */
    public function getDependencies(): array
    {
        return [
            LoadOrganization::class,
            LoadUser::class,
            LoadOtherOrganizations::class
        ];
    }

    private array $channelData = [
        [
            'name' => 'AuthorizeNet',
            'type' => 'authorize_net',
            'enabled' => true,
            'reference' => 'authorize_net:channel_1',
            'organization' => LoadOrganization::ORGANIZATION,
        ],
        [
            'name' => 'AuthorizeNet2',
            'type' => 'authorize_net',
            'enabled' => true,
            'reference' => 'authorize_net:channel_2',
            'organization' => LoadOrganization::ORGANIZATION,
        ],
        [
            'name' => 'AuthorizeNet3',
            'type' => 'authorize_net',
            'enabled' => false,
            'reference' => 'authorize_net:channel_3',
            'organization' => LoadOrganization::ORGANIZATION,
        ],
        [
            'name' => 'AuthorizeNet4',
            'type' => 'authorize_net',
            'enabled' => true,
            'reference' => 'authorize_net:channel_4',
            'organization' => LoadOtherOrganizations::ORGANIZATION_1
        ],
    ];

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager): void
    {
        $userManager = $this->container->get('oro_user.manager');
        $admin = $userManager->findUserByEmail(LoadAdminUserData::DEFAULT_ADMIN_EMAIL);

        foreach ($this->channelData as $data) {
            $entity = new Channel();
            $entity->setName($data['name']);
            $entity->setType($data['type']);
            $entity->setEnabled($data['enabled']);
            $entity->setDefaultUserOwner($admin);
            $entity->setOrganization($this->getReference($data['organization']));
            $entity->setTransport(new AuthorizeNetSettings());
            $this->setReference($data['reference'], $entity);

            $manager->persist($entity);
        }
        $manager->flush();
    }
}
