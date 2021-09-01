<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\Entity\Model\DTO;

use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Region;
use Oro\Bundle\AuthorizeNetBundle\Model\DTO\PaymentProfileAddressDTO;
use Oro\Component\Testing\Unit\EntityTestCaseTrait;

class PaymentProfileAddressDTOTest extends \PHPUnit\Framework\TestCase
{
    use EntityTestCaseTrait;

    public function testAccessorsForNull(): void
    {
        self::assertPropertyAccessors(
            new PaymentProfileAddressDTO(),
            [
                ['firstName', null],
                ['lastName', null],
                ['company', null],
                ['region', null],
                ['phoneNumber', null],
                ['faxNumber', null],
            ]
        );
    }

    public function testAccessors(): void
    {
        self::assertPropertyAccessors(
            new PaymentProfileAddressDTO(),
            [
                ['firstName', 'firstName'],
                ['lastName', 'lastName'],
                ['company', 'company'],
                ['street', 'street'],
                ['country', new Country('US')],
                ['region', new Region('USCODE')],
                ['zip', '12345'],
                ['phoneNumber', '12345'],
                ['faxNumber', '12345'],
            ]
        );
    }

    public function testGetRegionStringWithoutRegion(): void
    {
        $addressData = new PaymentProfileAddressDTO();

        self::assertEquals('', $addressData->getRegionString());
    }

    /**
     * @dataProvider getRegionStringProvider
     */
    public function testGetRegionString(
        string $iso2code,
        string $regionCode,
        string $regionName,
        string $expectedResult
    ): void {
        $addressData = new PaymentProfileAddressDTO();
        $country = new Country($iso2code);
        $region = new Region($iso2code.$regionCode);
        $region->setName($regionName);
        $region->setCode($regionCode);
        $region->setCountry($country);
        $addressData->setRegion($region);
        $addressData->setCountry($country);

        self::assertEquals($expectedResult, $addressData->getRegionString());
    }

    public function testGetCountryCodeWithoutCountry(): void
    {
        $addressData = new PaymentProfileAddressDTO();

        self::assertEquals('', $addressData->getCountryCode());
    }

    public function testGetCountryCode(): void
    {
        $addressData = new PaymentProfileAddressDTO();
        $country = new Country('US');
        $country->setIso3Code('USA');
        $addressData->setCountry($country);

        self::assertEquals('USA', $addressData->getCountryCode());
    }

    public function getRegionStringProvider(): array
    {
        return [
            'US' => [
                'iso2code' => 'US',
                'regionCode' => 'CODE',
                'regionName' => 'NAME',
                'expectedResult' => 'CODE'
            ],
            'FR' => [
                'iso2code' => 'FR',
                'regionCode' => 'CODE',
                'regionName' => 'NAME',
                'expectedResult' => 'NAME'
            ]
        ];
    }
}
