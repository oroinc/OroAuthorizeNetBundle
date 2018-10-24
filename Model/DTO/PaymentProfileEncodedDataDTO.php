<?php

namespace Oro\Bundle\AuthorizeNetBundle\Model\DTO;

/**
 * DTO/form model for tokenized credit card data
 */
class PaymentProfileEncodedDataDTO
{
    /** @var string */
    protected $descriptor;

    /** @var string */
    protected $value;

    /**
     * @return string
     */
    public function getDescriptor()
    {
        return $this->descriptor;
    }

    /**
     * @param string $descriptor
     * @return $this
     */
    public function setDescriptor($descriptor)
    {
        $this->descriptor = $descriptor;

        return $this;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }
}
