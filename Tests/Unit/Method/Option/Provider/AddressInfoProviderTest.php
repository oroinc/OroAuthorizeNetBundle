<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\Method\Option\Provider;

use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Region;
use Oro\Bundle\AuthorizeNetBundle\Method\Option\Provider\AddressInfoProvider;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\OrderBundle\Entity\OrderAddress;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;
use Oro\Bundle\PaymentBundle\Provider\AddressExtractor;
use Oro\Component\Testing\Unit\EntityTrait;

class AddressInfoProviderTest extends \PHPUnit\Framework\TestCase
{
    use EntityTrait;

    /** @var DoctrineHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $doctrineHelper;

    /** @var AddressExtractor|\PHPUnit\Framework\MockObject\MockObject */
    protected $addressExtractor;

    /** @var PaymentTransaction|\PHPUnit\Framework\MockObject\MockObject */
    protected $paymentTransaction;

    /** @var AddressInfoProvider */
    protected $provider;

    public function setUp()
    {
        $this->doctrineHelper = $this->createMock(DoctrineHelper::class);
        $this->addressExtractor = $this->createMock(AddressExtractor::class);

        $paymentTransaction = new PaymentTransaction();
        $paymentTransaction->setEntityClass(\stdClass::class);
        $paymentTransaction->setEntityIdentifier(1);

        $this->provider = new AddressInfoProvider(
            $this->doctrineHelper,
            $this->addressExtractor,
            $paymentTransaction
        );
    }

    public function testGetBillingAddressDtoExtractException()
    {
        $this->addressExtractor
            ->expects($this->once())
            ->method('extractAddress')
            ->willThrowException(
                new \InvalidArgumentException('Something went wrong')
            );

        $this->assertNull($this->provider->getBillingAddressDto());
    }

    public function testGetBillingAddressDtoWrongAddressEntity()
    {
        $this->addressExtractor
            ->expects($this->once())
            ->method('extractAddress')
            ->willReturn(new \stdClass());

        $this->assertNull($this->provider->getBillingAddressDto());
    }

    public function testGetBillingAddressDto()
    {
        $orderAddress = new OrderAddress();
        $orderAddress->setFirstName('John');
        $orderAddress->setLastName('Doe');
        $orderAddress->setOrganization('Oro');
        $orderAddress->setStreet('Elm');
        $orderAddress->setCountry(
            $this->getEntity(Country::class, ['iso3Code' => 'USA'], ['US'])
        );
        $orderAddress->setRegion(
            $this->getEntity(Region::class, ['name' => 'California'], ['CA'])
        );
        $orderAddress->setPostalCode('01001');
        $orderAddress->setCity('Los Angeles');
        $orderAddress->setPhone('+123456');

        $this->addressExtractor
            ->expects($this->once())
            ->method('extractAddress')
            ->willReturn($orderAddress);

        $addressDto = $this->provider->getBillingAddressDto();
        $this->assertNotNull($addressDto);
        $this->assertEquals('John', $addressDto->getFirstName());
        $this->assertEquals('Doe', $addressDto->getLastName());
        $this->assertEquals('Oro', (string) $addressDto->getCompany());
        $this->assertEquals('Elm', (string) $addressDto->getStreet());
        $this->assertEquals('USA', $addressDto->getCountryCode());
        $this->assertEquals('California', $addressDto->getRegionString());
        $this->assertEquals('Los Angeles', $addressDto->getCity());
        $this->assertEquals('+123456', $addressDto->getPhoneNumber());
        $this->assertEquals('', (string) $addressDto->getFaxNumber());
    }

    public function testGetShippingAddressDto()
    {
        $orderAddress = new OrderAddress();
        $orderAddress->setFirstName('John');
        $orderAddress->setLastName('Doe');
        $orderAddress->setOrganization('Oro');
        $orderAddress->setStreet('Elm');
        $orderAddress->setCountry(
            $this->getEntity(Country::class, ['iso3Code' => 'USA'], ['US'])
        );
        $orderAddress->setRegion(
            $this->getEntity(Region::class, ['name' => 'California'], ['CA'])
        );
        $orderAddress->setPostalCode('01001');
        $orderAddress->setCity('Los Angeles');
        $orderAddress->setPhone('+123456');

        $this->addressExtractor
            ->expects($this->once())
            ->method('extractAddress')
            ->willReturn($orderAddress);

        $addressDto = $this->provider->getShippingAddressDto();
        $this->assertNotNull($addressDto);
        $this->assertEquals('John', $addressDto->getFirstName());
        $this->assertEquals('Doe', $addressDto->getLastName());
        $this->assertEquals('Oro', (string) $addressDto->getCompany());
        $this->assertEquals('Elm', (string) $addressDto->getStreet());
        $this->assertEquals('USA', $addressDto->getCountryCode());
        $this->assertEquals('California', $addressDto->getRegionString());
        $this->assertEquals('Los Angeles', $addressDto->getCity());
        $this->assertEquals('+123456', $addressDto->getPhoneNumber());
        $this->assertEquals('', (string) $addressDto->getFaxNumber());
    }

    public function testGetShippingAddressDtoExtractException()
    {
        $this->addressExtractor
            ->expects($this->once())
            ->method('extractAddress')
            ->willThrowException(
                new \InvalidArgumentException('Something went wrong')
            );

        $this->assertNull($this->provider->getShippingAddressDto());
    }

    public function testGetShippingAddressDtoWrongEntity()
    {
        $this->addressExtractor
            ->expects($this->once())
            ->method('extractAddress')
            ->willReturn(new \stdClass());

        $this->assertNull($this->provider->getShippingAddressDto());
    }

    public function testTooLongShippingAddressIsTruncated()
    {
        $hundredLetterPhrase = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.'
            . ' Nam lacinia ex ut urna tincidunt, vel amet.';

        $orderAddress = new OrderAddress();
        $orderAddress->setFirstName($hundredLetterPhrase);
        $orderAddress->setLastName($hundredLetterPhrase);
        $orderAddress->setOrganization($hundredLetterPhrase);
        $orderAddress->setStreet($hundredLetterPhrase);
        $orderAddress->setCity($hundredLetterPhrase);
        $orderAddress->setPostalCode($hundredLetterPhrase);

        $orderAddress->setCountry(
            $this->getEntity(Country::class, ['iso3Code' => 'USA'], ['US'])
        );
        $orderAddress->setRegion(
            $this->getEntity(Region::class, ['name' => 'California'], ['CA'])
        );

        $this->addressExtractor
            ->expects($this->once())
            ->method('extractAddress')
            ->willReturn($orderAddress);

        $addressDto = $this->provider->getShippingAddressDto();

        $this->assertEquals(AddressInfoProvider::FIRST_NAME_MAX_LENGTH, mb_strlen($addressDto->getFirstName()));
        $this->assertEquals(AddressInfoProvider::LAST_NAME_MAX_LENGTH, mb_strlen($addressDto->getLastName()));
        $this->assertEquals(AddressInfoProvider::COMPANY_NAME_MAX_LENGTH, mb_strlen($addressDto->getCompany()));
        $this->assertEquals(AddressInfoProvider::CITY_NAME_MAX_LENGTH, mb_strlen($addressDto->getCity()));
        $this->assertEquals(AddressInfoProvider::STREET_MAX_LENGTH, mb_strlen($addressDto->getStreet()));
        $this->assertEquals(AddressInfoProvider::POST_CODE_MAX_LENGTH, mb_strlen($addressDto->getZip()));
    }

    public function testTooLongBillingAddressIsTruncated()
    {
        $hundredLetterPhrase = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.'
            . ' Nam lacinia ex ut urna tincidunt, vel amet.';

        $orderAddress = new OrderAddress();
        $orderAddress->setFirstName($hundredLetterPhrase);
        $orderAddress->setLastName($hundredLetterPhrase);
        $orderAddress->setOrganization($hundredLetterPhrase);
        $orderAddress->setStreet($hundredLetterPhrase);
        $orderAddress->setCity($hundredLetterPhrase);
        $orderAddress->setPostalCode($hundredLetterPhrase);

        $orderAddress->setCountry(
            $this->getEntity(Country::class, ['iso3Code' => 'USA'], ['US'])
        );
        $orderAddress->setRegion(
            $this->getEntity(Region::class, ['name' => 'California'], ['CA'])
        );

        $this->addressExtractor
            ->expects($this->once())
            ->method('extractAddress')
            ->willReturn($orderAddress);

        $addressDto = $this->provider->getBillingAddressDto();

        $this->assertEquals(AddressInfoProvider::FIRST_NAME_MAX_LENGTH, mb_strlen($addressDto->getFirstName()));
        $this->assertEquals(AddressInfoProvider::LAST_NAME_MAX_LENGTH, mb_strlen($addressDto->getLastName()));
        $this->assertEquals(AddressInfoProvider::COMPANY_NAME_MAX_LENGTH, mb_strlen($addressDto->getCompany()));
        $this->assertEquals(AddressInfoProvider::CITY_NAME_MAX_LENGTH, mb_strlen($addressDto->getCity()));
        $this->assertEquals(AddressInfoProvider::STREET_MAX_LENGTH, mb_strlen($addressDto->getStreet()));
        $this->assertEquals(AddressInfoProvider::POST_CODE_MAX_LENGTH, mb_strlen($addressDto->getZip()));
    }
}
