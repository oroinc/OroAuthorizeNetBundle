<?php

namespace Oro\Bundle\AuthorizeNetBundle\Model\DTO;

/**
 * DTO/form model for masked bank account data
 */
class PaymentProfileMaskedDataDTO
{
    /** @var string */
    protected $accountNumber;

    /** @var string */
    protected $routingNumber;

    /** @var string */
    protected $nameOnAccount;

    /** @var string */
    protected $accountType;

    /** @var string */
    protected $bankName;

    /**
     * @return string
     */
    public function getAccountNumber()
    {
        return $this->accountNumber;
    }

    /**
     * @param string $accountNumber
     * @return $this
     */
    public function setAccountNumber($accountNumber)
    {
        $this->accountNumber = $accountNumber;

        return $this;
    }

    /**
     * @return string
     */
    public function getRoutingNumber()
    {
        return $this->routingNumber;
    }

    /**
     * @param string $routingNumber
     * @return $this
     */
    public function setRoutingNumber($routingNumber)
    {
        $this->routingNumber = $routingNumber;

        return $this;
    }

    /**
     * @return string
     */
    public function getNameOnAccount()
    {
        return $this->nameOnAccount;
    }

    /**
     * @param string $nameOnAccount
     * @return $this
     */
    public function setNameOnAccount($nameOnAccount)
    {
        $this->nameOnAccount = $nameOnAccount;

        return $this;
    }

    /**
     * @return string
     */
    public function getAccountType()
    {
        return $this->accountType;
    }

    /**
     * @param string $accountType
     * @return $this
     */
    public function setAccountType($accountType)
    {
        $this->accountType = $accountType;

        return $this;
    }

    /**
     * @return string
     */
    public function getBankName()
    {
        return (string) $this->bankName;
    }

    /**
     * @param string|null $bankName
     * @return $this
     */
    public function setBankName($bankName)
    {
        $this->bankName = $bankName;

        return $this;
    }
}
