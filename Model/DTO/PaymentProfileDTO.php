<?php

namespace Oro\Bundle\AuthorizeNetBundle\Model\DTO;

use Oro\Bundle\AuthorizeNetBundle\Entity\CustomerPaymentProfile;

/**
 * DTO/form model for full payment profile form
 */
class PaymentProfileDTO
{
    /** @var  CustomerPaymentProfile */
    protected $profile;

    /** @var PaymentProfileAddressDTO */
    protected $address;

    /** @var bool */
    protected $updatePaymentData;

    /** @var PaymentProfileEncodedDataDTO */
    protected $encodedData;

    /** @var PaymentProfileMaskedDataDTO */
    protected $maskedData;

    public function __construct(CustomerPaymentProfile $profile = null)
    {
        $this->profile = $profile ?: new CustomerPaymentProfile();
        $this->address = new PaymentProfileAddressDTO();
        $this->encodedData = new PaymentProfileEncodedDataDTO();
        $this->maskedData = new PaymentProfileMaskedDataDTO();
        $this->updatePaymentData = !$this->profile->getId();
    }

    /**
     * @return CustomerPaymentProfile
     */
    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * @param CustomerPaymentProfile $profile
     * @return $this
     */
    public function setProfile(CustomerPaymentProfile $profile)
    {
        $this->profile = $profile;

        return $this;
    }

    /**
     * @return PaymentProfileAddressDTO
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param PaymentProfileAddressDTO $address
     * @return $this
     */
    public function setAddress(PaymentProfileAddressDTO $address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @return bool
     */
    public function isUpdatePaymentData()
    {
        return $this->updatePaymentData;
    }

    /**
     * @param bool $updatePaymentData
     * @return $this
     */
    public function setUpdatePaymentData($updatePaymentData)
    {
        $this->updatePaymentData = (bool) $updatePaymentData;

        return $this;
    }

    /**
     * @return PaymentProfileEncodedDataDTO
     */
    public function getEncodedData()
    {
        return $this->encodedData;
    }

    /**
     * @param PaymentProfileEncodedDataDTO $encodedData
     * @return $this
     */
    public function setEncodedData(PaymentProfileEncodedDataDTO $encodedData)
    {
        $this->encodedData = $encodedData;

        return $this;
    }

    /**
     * @return PaymentProfileMaskedDataDTO
     */
    public function getMaskedData()
    {
        return $this->maskedData;
    }

    /**
     * @param PaymentProfileMaskedDataDTO $maskedData
     * @return $this
     */
    public function setMaskedData(PaymentProfileMaskedDataDTO $maskedData)
    {
        $this->maskedData = $maskedData;

        return $this;
    }
}
