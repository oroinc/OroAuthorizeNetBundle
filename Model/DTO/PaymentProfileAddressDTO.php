<?php

namespace Oro\Bundle\AuthorizeNetBundle\Model\DTO;

use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Region;

/**
 * DTO/form model for payment profile address
 */
class PaymentProfileAddressDTO
{
    protected ?string $firstName = null;

    protected ?string $lastName = null;

    protected ?string $company = null;

    protected ?string $street = null;

    protected ?Country $country = null;

    protected ?Region $region = null;

    protected ?string $region_text = null;

    protected ?string $city = null;

    protected ?string $zip = null;

    protected ?string $phoneNumber = null;

    protected ?string $faxNumber = null;

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getCompany(): ?string
    {
        return $this->company;
    }

    public function setCompany(?string $company): self
    {
        $this->company = $company;

        return $this;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(string $street): self
    {
        $this->street = $street;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getZip(): ?string
    {
        return $this->zip;
    }

    public function setZip(string $zip): self
    {
        $this->zip = $zip;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): self
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function getFaxNumber(): ?string
    {
        return $this->faxNumber;
    }

    public function setFaxNumber(?string $faxNumber): self
    {
        $this->faxNumber = $faxNumber;

        return $this;
    }

    public function getCountry(): ?Country
    {
        return $this->country;
    }

    public function setCountry(Country $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getRegion(): ?Region
    {
        return $this->region;
    }

    public function setRegion(?Region $region): self
    {
        $this->region = $region;

        return $this;
    }

    /**
     * Get region string depend on country (name or code for US)
     */
    public function getRegionString(): string
    {
        if (!$this->region) {
            return '';
        }

        return $this->region->getCountryIso2Code() === 'US' ? $this->region->getCode() : $this->region->getName();
    }

    /**
     * Get country string (Country::Iso3Code)
     */
    public function getCountryCode(): string
    {
        return $this->country ? $this->country->getIso3Code() : '';
    }

    public function getRegionText(): ?string
    {
        return $this->region_text;
    }

    public function setRegionText(string $region_text): self
    {
        $this->region_text = $region_text;

        return $this;
    }
}
