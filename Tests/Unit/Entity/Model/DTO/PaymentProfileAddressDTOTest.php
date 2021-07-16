<?php

namespace Oro\Bundle\AuthorizeNetBundle\Tests\Unit\Entity\Model\DTO;

use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Region;
use Oro\Bundle\AuthorizeNetBundle\Model\DTO\PaymentProfileAddressDTO;

class PaymentProfileAddressDTOTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider getRegionStringProvider
     */
    public function testGetRegionString($iso2code, $regionCode, $regionName, $expectedResult)
    {
        $addressData = new PaymentProfileAddressDTO();
        $country = new Country($iso2code);
        $region = new Region($iso2code.$regionCode);
        $region->setName($regionName);
        $region->setCode($regionCode);
        $region->setCountry($country);
        $addressData->setRegion($region);
        $addressData->setCountry($country);

        $this->assertEquals($expectedResult, $addressData->getRegionString());
    }

    public function testGetCountryCode()
    {
        $addressData = new PaymentProfileAddressDTO();
        $country = new Country('US');
        $country->setIso3Code('USA');
        $addressData->setCountry($country);

        $this->assertEquals('USA', $addressData->getCountryCode());
    }

    /**
     * @return array
     */
    public function getRegionStringProvider()
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
