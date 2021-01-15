<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\Service;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\AuthorizeNetBundle\Entity\AuthorizeNetSettings;
use Oro\Bundle\AuthorizeNetBundle\Service\TransactionKeyValueProvider;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\SecurityBundle\Encoder\SymmetricCrypterInterface;

class TransactionKeyValueProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider integrationEditFormValueDataProvider
     */
    public function testFromIntegrationEditFormValue($value, $decryptedValue, $integrationId, $expected)
    {
        $settings = $this->createMock(AuthorizeNetSettings::class);
        $settings
            ->expects($this->exactly($integrationId === null ? 0 : 1))
            ->method('getTransactionKey')
            ->willReturn($value);

        $channel = $this->createMock(Channel::class);
        $channel
            ->expects($this->exactly($integrationId === null ? 0 : 1))
            ->method('getTransport')
            ->willReturn($settings);

        $em = $this->createMock(EntityManagerInterface::class);
        $em
            ->expects($this->exactly($integrationId === null ? 0 : 1))
            ->method('find')
            ->with(Channel::class, $integrationId)
            ->willReturn($channel);

        $registry = $this->createMock(ManagerRegistry::class);
        $registry
            ->expects($this->exactly($integrationId === null ? 0 : 1))
            ->method('getManagerForClass')
            ->willReturn($em);

        $crypter = $this->createMock(SymmetricCrypterInterface::class);
        $crypter
            ->expects($this->exactly($integrationId === null ? 0 : 1))
            ->method('decryptData')
            ->with($value)
            ->willReturn($decryptedValue);

        $provider = new TransactionKeyValueProvider($registry, $crypter);

        self::assertEquals($expected, $provider->fromIntegrationEditFormValue($integrationId, $value));
    }

    public function integrationEditFormValueDataProvider()
    {
        return [
            'Value does not contain asterisks' => [
                'new_value_1', 'test_1', 1, 'new_value_1'
            ],
            'Value contains asterisks and count of asterisks equals count of elements of stored value' => [
                '******', 'test_2', 2, 'test_2'
            ],
            'Value contains asterisks and count of asterisks does not equal count of elements of stored value' => [
                '****', 'test_3', 3, '****'
            ],
            'During create process (integration = NULL)' => [
                'test_value', null, null, 'test_value'
            ],
        ];
    }
}
