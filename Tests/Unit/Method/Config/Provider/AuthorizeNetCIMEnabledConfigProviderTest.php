<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\Method\Config\Provider;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\AuthorizeNetBundle\Method\Config\AuthorizeNetConfig;
use Oro\Bundle\AuthorizeNetBundle\Method\Config\AuthorizeNetConfigInterface;
use Oro\Bundle\AuthorizeNetBundle\Method\Config\Provider\AuthorizeNetCIMEnabledConfigProvider;
use Oro\Bundle\AuthorizeNetBundle\Method\Config\Provider\AuthorizeNetConfigProviderInterface;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Component\Testing\Unit\EntityTrait;

class AuthorizeNetCIMEnabledConfigProviderTest extends \PHPUnit\Framework\TestCase
{
    use EntityTrait;

    /** @var AuthorizeNetCIMEnabledConfigProvider */
    private $CIMEnabledConfigProvider;

    /** @var AuthorizeNetConfigProviderInterface | \PHPunit\Framework\MockObject\MockObject */
    private $configProvider;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->configProvider = $this->createMock(AuthorizeNetConfigProviderInterface::class);
        $this->CIMEnabledConfigProvider = new AuthorizeNetCIMEnabledConfigProvider($this->configProvider);
    }

    /**
     * @dataProvider getPaymentConfigWithEnabledCIMByWebsiteProvider
     *
     * @param array                            $paymentConfigsParams
     * @param Website                          $website
     * @param AuthorizeNetConfigInterface|null $expectedResult
     */
    public function testGetPaymentConfigWithEnabledCIMByWebsite(
        array $paymentConfigsParams,
        Website $website,
        AuthorizeNetConfigInterface $expectedResult = null
    ) {
        $paymentConfigs = array_map([$this, 'getConfigsByConfigsParams'], $paymentConfigsParams);
        $this->configProvider
            ->expects($this->once())
            ->method('getPaymentConfigs')
            ->willReturn($paymentConfigs);

        $actualResult = $this->CIMEnabledConfigProvider->getPaymentConfigWithEnabledCIMByWebsite($website);
        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * @dataProvider getPaymentConfigWithEnabledCIMByWebsiteProvider
     *
     * @param array                            $paymentConfigsParams
     * @param Website                          $website
     * @param AuthorizeNetConfigInterface|null $expectedResult
     */
    public function testHasPaymentWithEnabledCIMByWebsite(
        array $paymentConfigsParams,
        Website $website,
        AuthorizeNetConfigInterface $expectedResult = null
    ) {
        $paymentConfigs = array_map([$this, 'getConfigsByConfigsParams'], $paymentConfigsParams);
        $this->configProvider
            ->expects($this->once())
            ->method('getPaymentConfigs')
            ->willReturn($paymentConfigs);

        $actualResult = $this->CIMEnabledConfigProvider->hasPaymentWithEnabledCIMByWebsite($website);

        if (null === $expectedResult) {
            $this->assertFalse($actualResult);
        } else {
            $this->assertTrue($actualResult);
        }
    }

    /**
     * @return array
     */
    public function getPaymentConfigWithEnabledCIMByWebsiteProvider()
    {
        $applicableWebsite = $this->getEntity(Website::class, ['id' => 1]);
        $notApplicableWebsite = $this->getEntity(Website::class, ['id' => 2]);

        return [
            'Has no payment configs' => [
                'paymentConfigsParams' => [],
                'website' => $applicableWebsite,
                'expectedResult' => null
            ],
            'Has several payment configs, but any with cim enabled functionality' => [
                'paymentConfigsParams' => [
                    [
                        AuthorizeNetConfig::ENABLED_CIM_KEY => false
                    ],
                    [
                        AuthorizeNetConfig::ENABLED_CIM_KEY => false,
                        AuthorizeNetConfig::ENABLED_CIM_WEBSITES => new ArrayCollection([
                            $notApplicableWebsite
                        ])
                    ]
                ],
                'website' => $applicableWebsite,
                'expectedResult' => null
            ],
            'Has several payment configs, but any with applicable website' => [
                'paymentConfigsParams' => [
                    [
                        AuthorizeNetConfig::ENABLED_CIM_KEY => true,
                        AuthorizeNetConfig::ENABLED_CIM_WEBSITES => new ArrayCollection([
                            $notApplicableWebsite
                        ])
                    ]
                ],
                'website' => $applicableWebsite,
                'expectedResult' => null
            ],
            'Has payment config with applicable website' => [
                'paymentConfigsParams' => [
                    [
                        AuthorizeNetConfig::ENABLED_CIM_KEY => true,
                        AuthorizeNetConfig::ENABLED_CIM_WEBSITES => new ArrayCollection([
                            $applicableWebsite,
                            $notApplicableWebsite
                        ])
                    ]
                ],
                'website' => $applicableWebsite,
                'expectedResult' => $this->getConfigsByConfigsParams([
                    AuthorizeNetConfig::ENABLED_CIM_KEY => true,
                    AuthorizeNetConfig::ENABLED_CIM_WEBSITES => new ArrayCollection([
                        $applicableWebsite,
                        $notApplicableWebsite
                    ])
                ])
            ]
        ];
    }

    /**
     * @param array $paymentConfigParams
     *
     * @return AuthorizeNetConfigInterface
     */
    private function getConfigsByConfigsParams(array $paymentConfigParams)
    {
        $config = new AuthorizeNetConfig();
        $config->add($paymentConfigParams);

        return $config;
    }
}
