<?php

namespace Oro\Bundle\AuthorizeNetBundle\Model\DTO;

use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Region;

/**
 * DTO/form model for payment profile address)
 */
class PaymentProfileAddressDTO
{
    /** @var string */
    protected $firstName;

    /** @var string */
    protected $lastName;

    /** @var string */
    protected $company;

    /** @var string */
    protected $street;

    /** @var Country|null */
    protected $country;

    /** @var Region|null */
    protected $region;

    /** @var string */
    protected $region_text;

    /** @var string */
    protected $city;

    /** @var string */
    protected $zip;

    /** @var string */
    protected $phoneNumber;

    /** @var string */
    protected $faxNumber;

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     * @return $this
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     * @return $this
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * @return string
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * @param string $company
     * @return $this
     */
    public function setCompany($company)
    {
        $this->company = $company;

        return $this;
    }

    /**
     * @return string
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * @param string $street
     * @return $this
     */
    public function setStreet($street)
    {
        $this->street = $street;

        return $this;
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param string $city
     * @return $this
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * @return string
     */
    public function getZip()
    {
        return $this->zip;
    }

    /**
     * @param string $zip
     * @return $this
     */
    public function setZip($zip)
    {
        $this->zip = $zip;

        return $this;
    }

    /**
     * @return string
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * @param string $phoneNumber
     * @return $this
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    /**
     * @return string
     */
    public function getFaxNumber()
    {
        return $this->faxNumber;
    }

    /**
     * @param string $faxNumber
     * @return $this
     */
    public function setFaxNumber($faxNumber)
    {
        $this->faxNumber = $faxNumber;

        return $this;
    }

    /**
     * @return Country|null
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param Country $country
     * @return $this
     */
    public function setCountry(Country $country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * @return Region
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * @param Region $region
     * @return $this
     */
    public function setRegion(Region $region)
    {
        $this->region = $region;

        return $this;
    }

    /**
     * get region string depend on country (name or code for US)
     * @return string
     */
    public function getRegionString()
    {
        $region = $this->region;
        $regionString = '';

        if ($region) {
            if ($region->getCountryIso2Code() === 'US') {
                $regionString = $region->getCode();
            } else {
                $regionString = $region->getName();
            }
        }

        return $regionString;
    }

    /**
     * get country string (Country::Iso3Code)
     * @return string
     */
    public function getCountryCode()
    {
        $country = $this->country;

        return $country ? $country->getIso3Code() : '';
    }

    /**
     * @return string
     */
    public function getRegionText()
    {
        return $this->region_text;
    }

    /**
     * @param string $region_text
     */
    public function setRegionText(string $region_text)
    {
        $this->region_text = $region_text;
    }
}
