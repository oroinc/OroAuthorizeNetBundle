<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Functional\Entity\Repository;

use Oro\Bundle\AuthorizeNetBundle\Entity\AuthorizeNetSettings;
use Oro\Bundle\AuthorizeNetBundle\Entity\Repository\AuthorizeNetSettingsRepository;
use Oro\Bundle\AuthorizeNetBundle\Tests\Functional\DataFixtures\LoadAuthorizeNetChannelData;
use Oro\Bundle\SecurityBundle\Authentication\Token\UsernamePasswordOrganizationToken;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadOrganization;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadUser;

class AuthorizeNetSettingsRepositoryTest extends WebTestCase
{
    private AuthorizeNetSettingsRepository $repository;

    protected function setUp(): void
    {
        $this->initClient([], self::generateBasicAuthHeader());
        $this->loadFixtures([LoadAuthorizeNetChannelData::class]);

        $this->repository = self::getContainer()->get('doctrine')
            ->getRepository(AuthorizeNetSettings::class);
    }

    public function testGetEnabledSettingsByType()
    {
        $organization = $this->getReference(LoadOrganization::ORGANIZATION);
        $adminToken = new UsernamePasswordOrganizationToken(
            $this->getReference(LoadUser::USER),
            'key',
            $organization
        );

        $this->getContainer()->get('security.token_storage')->setToken($adminToken);

        $enabledSettings = $this->repository->getEnabledSettingsByType('authorize_net');
        self::assertCount(2, $enabledSettings);
    }
}
