<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Functional\Entity\Repository;

use Oro\Bundle\AuthorizeNetBundle\Entity\AuthorizeNetSettings;
use Oro\Bundle\AuthorizeNetBundle\Entity\Repository\AuthorizeNetSettingsRepository;
use Oro\Bundle\AuthorizeNetBundle\Tests\Functional\DataFixtures\LoadAuthorizeNetChannelData;
use Oro\Bundle\SecurityBundle\Authentication\Token\UsernamePasswordOrganizationToken;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\UserBundle\Migrations\Data\ORM\LoadAdminUserData;

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
        $userManager = self::getContainer()->get('oro_user.manager');
        $admin = $userManager->findUserByEmail(LoadAdminUserData::DEFAULT_ADMIN_EMAIL);

        $token = new UsernamePasswordOrganizationToken(
            $admin,
            'admin',
            'main',
            $admin->getOrganization(),
            $admin->getRoles()
        );

        $this->getContainer()->get('security.token_storage')->setToken($token);

        $enabledSettings = $this->repository->getEnabledSettingsByType('authorize_net');
        self::assertCount(2, $enabledSettings);
    }
}
