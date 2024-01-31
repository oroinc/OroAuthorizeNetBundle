<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\AuthorizeNetBundle\Entity\AuthorizeNetSettings;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadOrganization;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadUser;

class LoadAuthorizeNetChannelData extends AbstractFixture implements DependentFixtureInterface
{
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
    public function getDependencies(): array
    {
        return [LoadOrganization::class, LoadUser::class];
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager): void
    {
        foreach ($this->channelData as $data) {
            $entity = new Channel();
            $entity->setName($data['name']);
            $entity->setType($data['type']);
            $entity->setEnabled($data['enabled']);
            $entity->setDefaultUserOwner($this->getReference(LoadUser::USER));
            $entity->setOrganization($this->getReference(LoadOrganization::ORGANIZATION));
            $entity->setTransport(new AuthorizeNetSettings());
            $this->setReference($data['reference'], $entity);
            $manager->persist($entity);
        }
        $manager->flush();
    }
}
