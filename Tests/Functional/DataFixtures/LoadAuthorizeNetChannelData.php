<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\AuthorizeNetBundle\Entity\AuthorizeNetSettings;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\UserBundle\Migrations\Data\ORM\LoadAdminUserData;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class LoadAuthorizeNetChannelData extends AbstractFixture implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    private array $channelData = [
        [
            'name' => 'AuthorizeNet',
            'type' => 'authorize_net',
            'enabled' => true,
            'reference' => 'authorize_net:channel_1',
        ],
        [
            'name' => 'AuthorizeNet2',
            'type' => 'authorize_net',
            'enabled' => true,
            'reference' => 'authorize_net:channel_2',
        ],
        [
            'name' => 'AuthorizeNet3',
            'type' => 'authorize_net',
            'enabled' => false,
            'reference' => 'authorize_net:channel_3',
        ],
    ];

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager): void
    {
        $userManager = $this->container->get('oro_user.manager');
        $admin = $userManager->findUserByEmail(LoadAdminUserData::DEFAULT_ADMIN_EMAIL);
        $organization = $manager->getRepository(Organization::class)->getFirst();

        foreach ($this->channelData as $data) {
            $entity = new Channel();
            $entity->setName($data['name']);
            $entity->setType($data['type']);
            $entity->setEnabled($data['enabled']);
            $entity->setDefaultUserOwner($admin);
            $entity->setOrganization($organization);
            $entity->setTransport(new AuthorizeNetSettings());
            $this->setReference($data['reference'], $entity);

            $manager->persist($entity);
        }
        $manager->flush();
    }
}
