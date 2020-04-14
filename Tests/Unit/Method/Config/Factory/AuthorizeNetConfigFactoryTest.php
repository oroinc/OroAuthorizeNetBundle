<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\Method\Config\Factory;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\AuthorizeNetBundle\Entity\AuthorizeNetSettings;
use Oro\Bundle\AuthorizeNetBundle\Method\Config\AuthorizeNetConfig;
use Oro\Bundle\AuthorizeNetBundle\Method\Config\Factory\AuthorizeNetConfigFactory;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Generator\IntegrationIdentifierGeneratorInterface;
use Oro\Bundle\LocaleBundle\Entity\LocalizedFallbackValue;
use Oro\Bundle\LocaleBundle\Helper\LocalizationHelper;
use Oro\Bundle\SecurityBundle\Encoder\SymmetricCrypterInterface;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Component\Testing\Unit\EntityTrait;

class AuthorizeNetConfigFactoryTest extends \PHPUnit\Framework\TestCase
{
    use EntityTrait;

    /**
     * @var SymmetricCrypterInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $encoder;

    /**
     * @var LocalizationHelper|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $localizationHelper;

    /**
     * @var IntegrationIdentifierGeneratorInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $identifierGenerator;

    /**
     * @var AuthorizeNetConfigFactory
     */
    protected $authorizeNetConfigFactory;

    protected function setUp(): void
    {
        $this->encoder = $this->createMock(SymmetricCrypterInterface::class);
        $this->localizationHelper = $this->createMock(LocalizationHelper::class);
        $this->identifierGenerator = $this->createMock(IntegrationIdentifierGeneratorInterface::class);
        $this->authorizeNetConfigFactory = new AuthorizeNetConfigFactory(
            $this->encoder,
            $this->localizationHelper,
            $this->identifierGenerator
        );
    }

    public function testCreateConfig()
    {
        $this->encoder->expects($this->any())
            ->method('decryptData')
            ->willReturnMap(
                [
                    ['api login id', 'api login id'],
                    ['trans key', 'trans key'],
                    ['client key', 'client key'],
                ]
            );

        /** @var Channel $channel */
        $channel = $this->getEntity(
            Channel::class,
            ['id' => 1, 'name' => 'authorize_net']
        );

        $bag = [
            'channel' => $channel,
            'creditCardLabels' => [new LocalizedFallbackValue()],
            'creditCardShortLabels' => [new LocalizedFallbackValue()],
            'apiLoginId' => 'api login id',
            'transactionKey' => 'trans key',
            'clientKey' => 'client key',
            'allowedCreditCardTypes' => ['visa'],
            'creditCardPaymentAction' => 'charge',
            'enabled_cim' => true,
            'enabled_cim_websites' =>  new ArrayCollection([
                $this->getEntity(Website::class, ['id' => 1])
            ]),
            'echeck_enabled' => true,
            'echeck_account_types' => ['type1', 'type2'],
            'echeck_confirmation_text' => 'text'
        ];
        /** @var AuthorizeNetSettings $authorizeNetSettings */
        $authorizeNetSettings = $this->getEntity(AuthorizeNetSettings::class, $bag);
        $authorizeNetSettings->setAuthNetTestMode(true);

        $this->localizationHelper->expects($this->exactly(2))
            ->method('getLocalizedValue')
            ->willReturnMap(
                [
                    [$authorizeNetSettings->getCreditCardLabels(), null, 'test label'],
                    [$authorizeNetSettings->getCreditCardShortLabels(), null, 'test short label'],
                ]
            );

        $this->identifierGenerator->expects($this->once())
            ->method('generateIdentifier')
            ->with($channel)
            ->willReturn('authorize_net_1');

        $config = $this->authorizeNetConfigFactory->createConfig($authorizeNetSettings);

        $this->assertEquals(new AuthorizeNetConfig([
            'payment_method_identifier' => 'authorize_net_1',
            'admin_label' => 'authorize_net',
            'label' => 'test label',
            'short_label' => 'test short label',
            'allowed_credit_card_types' => ['visa'],
            'test_mode' => true,
            'purchase_action' => 'charge',
            'client_key' => 'client key',
            'api_login_id' => 'api login id',
            'transaction_key' => 'trans key',
            'require_cvv_entry' => true,
            'enabled_cim' => true,
            'enabled_cim_websites' => new ArrayCollection(
                [
                    $this->getEntity(Website::class, ['id' => 1])
                ]
            ),
            'integration_id' => 1,
            'echeck_enabled' => true,
            'echeck_account_types' => ['type1', 'type2'],
            'echeck_confirmation_text' => 'text'

        ]), $config);
    }
}
