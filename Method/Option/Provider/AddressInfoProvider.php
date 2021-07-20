<?php

namespace Oro\Bundle\AuthorizeNetBundle\Method\Option\Provider;

use Oro\Bundle\AuthorizeNetBundle\Model\DTO\PaymentProfileAddressDTO;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\OrderBundle\Entity\OrderAddress;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;
use Oro\Bundle\PaymentBundle\Provider\AddressExtractor;

/**
 * Allow fetch fields for <BillTo> container
 * Gets info from order, bound to paymentTransaction
 */
class AddressInfoProvider
{
    const BILLING_ADDRESS_PROPERTY = 'billingAddress';
    const SHIPPING_ADDRESS_PROPERTY = 'shippingAddress';

    const FIRST_NAME_MAX_LENGTH = 50;
    const LAST_NAME_MAX_LENGTH = 50;
    const COMPANY_NAME_MAX_LENGTH = 50;
    const STREET_MAX_LENGTH = 60;
    const CITY_NAME_MAX_LENGTH = 40;
    const POST_CODE_MAX_LENGTH = 20;

    /** @var DoctrineHelper */
    private $doctrineHelper;

    /** @var AddressExtractor */
    protected $addressExtractor;

    /** @var PaymentTransaction */
    protected $paymentTransaction;

    /**
     * @param DoctrineHelper $doctrineHelper
     * @param AddressExtractor $addressExtractor
     * @param PaymentTransaction $paymentTransaction
     */
    public function __construct(DoctrineHelper $doctrineHelper, AddressExtractor $addressExtractor, $paymentTransaction)
    {
        $this->doctrineHelper = $doctrineHelper;
        $this->addressExtractor = $addressExtractor;
        $this->paymentTransaction = $paymentTransaction;
    }

    /**
     * @return object
     */
    private function getEntity()
    {
        return $this->doctrineHelper->getEntityReference(
            $this->paymentTransaction->getEntityClass(),
            $this->paymentTransaction->getEntityIdentifier()
        );
    }

    private function getAddress(string $property): ?OrderAddress
    {
        try {
            $address = $this->addressExtractor->extractAddress($this->getEntity(), $property);
        } catch (\InvalidArgumentException $e) {
            return null;
        }

        /** @var OrderAddress $billingAddress */
        if (!$address instanceof OrderAddress) {
            return null;
        }

        return $address;
    }

    private function getAddressDto(string $property): ?PaymentProfileAddressDTO
    {
        $funcTruncate = function (?string $value, int $length) {
            return mb_substr($value, 0, $length);
        };

        $address = $this->getAddress($property);
        if (!$address) {
            return null;
        }

        return (new PaymentProfileAddressDTO)
            ->setFirstName($funcTruncate($address->getFirstName(), self::FIRST_NAME_MAX_LENGTH))
            ->setLastName($funcTruncate($address->getLastName(), self::LAST_NAME_MAX_LENGTH))
            ->setCompany($funcTruncate($address->getOrganization(), self::COMPANY_NAME_MAX_LENGTH))
            ->setStreet($funcTruncate($address->getStreet(), self::STREET_MAX_LENGTH))
            ->setCountry($address->getCountry())
            ->setRegion($address->getRegion())
            ->setZip($funcTruncate($address->getPostalCode(), self::POST_CODE_MAX_LENGTH))
            ->setCity($funcTruncate($address->getCity(), self::CITY_NAME_MAX_LENGTH))
            ->setPhoneNumber($address->getPhone());
    }

    public function getBillingAddressDto(): ?PaymentProfileAddressDTO
    {
        return $this->getAddressDto(self::BILLING_ADDRESS_PROPERTY);
    }

    public function getShippingAddressDto(): ?PaymentProfileAddressDTO
    {
        return $this->getAddressDto(self::SHIPPING_ADDRESS_PROPERTY);
    }
}
