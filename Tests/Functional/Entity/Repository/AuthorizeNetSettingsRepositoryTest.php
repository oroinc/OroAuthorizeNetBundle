<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Functional\Entity\Repository;

use Oro\Bundle\AuthorizeNetBundle\Entity\AuthorizeNetSettings;
use Oro\Bundle\AuthorizeNetBundle\Entity\Repository\AuthorizeNetSettingsRepository;
use Oro\Bundle\AuthorizeNetBundle\Tests\Functional\DataFixtures\LoadAuthorizeNetChannelData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class AuthorizeNetSettingsRepositoryTest extends WebTestCase
{
    private AuthorizeNetSettingsRepository $repository;

    #[\Override]
    protected function setUp(): void
    {
        $this->initClient([], self::generateBasicAuthHeader());
        $this->loadFixtures([LoadAuthorizeNetChannelData::class]);

        $this->repository = self::getContainer()->get('doctrine')
            ->getRepository(AuthorizeNetSettings::class);
    }

    public function testGetEnabledSettingsByType()
    {
        $enabledSettings = $this->repository->getEnabledSettingsByType('authorize_net');
        self::assertCount(2, $enabledSettings);
    }
}
