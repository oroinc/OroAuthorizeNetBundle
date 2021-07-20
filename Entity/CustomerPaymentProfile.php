<?php

namespace Oro\Bundle\AuthorizeNetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\AuthorizeNetBundle\Model\ExtendCustomerPaymentProfile;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\OrganizationBundle\Entity\OrganizationAwareInterface;
use Oro\Bundle\OrganizationBundle\Entity\Ownership\OrganizationAwareTrait;

/**
 * Entity that consist information "customerPaymentProfileId" from Authorize.Net
 * and binds it to Oro CustomerProfile entity
 * @ORM\Table(
 *     name="oro_au_net_payment_profile",
 *     indexes={
 *          @ORM\Index(name="oro_au_net_payment_profile_name_idx", columns={"name"}),
 *          @ORM\Index(name="oro_au_net_payment_profile_type_idx", columns={"type"})
 *     }
 * )
 * @ORM\Entity
 * @Config(
 *       mode="hidden",
 *       defaultValues={
 *          "ownership"={
 *              "owner_type"="ORGANIZATION",
 *              "owner_field_name"="organization",
 *              "owner_column_name"="organization_id",
 *              "frontend_owner_type"="FRONTEND_USER",
 *              "frontend_owner_field_name"="customerUser",
 *              "frontend_owner_column_name"="customer_user_id"
 *          },
 *          "security"={
 *              "type"="ACL",
 *              "group_name"="commerce"
 *          }
 *      }
 * )
 */
class CustomerPaymentProfile extends ExtendCustomerPaymentProfile implements OrganizationAwareInterface
{
    use OrganizationAwareTrait;

    const TYPE_CREDITCARD = 'creditcard';
    const TYPE_ECHECK = 'echeck';

    const ALLOWED_TYPES = [
        self::TYPE_CREDITCARD,
        self::TYPE_ECHECK
    ];

    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(name="type", length=10, type="string", options={"default"="creditcard"})
     */
    protected $type = self::TYPE_CREDITCARD;

    /**
     * @var string
     * @ORM\Column(name="name", length=25, type="string")
     */
    protected $name;

    /**
     * @var string
     * @ORM\Column(name="last_digits", length=4, type="string")
     */
    protected $lastDigits;

    /**
     * @var bool
     * @ORM\Column(name="is_default", type="boolean", options={"default"=false})
     */
    protected $default = false;

    /**
     * @var string
     * @ORM\Column(name="customer_payment_profile_id", length=32, type="string")
     */
    protected $customerPaymentProfileId;

    /**
     * @var CustomerProfile
     * @ORM\ManyToOne(
     *     targetEntity="Oro\Bundle\AuthorizeNetBundle\Entity\CustomerProfile",
     *     inversedBy="paymentProfiles"
     * )
     * @ORM\JoinColumn(name="customer_profile_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    protected $customerProfile;

    /**
     * @var CustomerUser
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\CustomerBundle\Entity\CustomerUser")
     * @ORM\JoinColumn(name="customer_user_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    protected $customerUser;

    /**
     * @param string $type
     */
    public function __construct($type = self::TYPE_CREDITCARD)
    {
        if (!\in_array($type, self::ALLOWED_TYPES, true)) {
            $type = self::TYPE_CREDITCARD;
        }
        $this->type = $type;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getLastDigits()
    {
        return $this->lastDigits;
    }

    /**
     * @param string $lastDigits
     */
    public function setLastDigits($lastDigits)
    {
        $this->lastDigits = $lastDigits;
    }

    /**
     * @return bool
     */
    public function isDefault()
    {
        return $this->default;
    }

    /**
     * @param bool $default
     * @return $this
     */
    public function setDefault($default)
    {
        $this->default = (bool) $default;

        return $this;
    }

    /**
     * @return string
     */
    public function getCustomerPaymentProfileId()
    {
        return $this->customerPaymentProfileId;
    }

    /**
     * @param string $customerPaymentProfileId
     * @return $this
     */
    public function setCustomerPaymentProfileId($customerPaymentProfileId)
    {
        $this->customerPaymentProfileId = $customerPaymentProfileId;

        return $this;
    }

    /**
     * @return CustomerProfile
     */
    public function getCustomerProfile()
    {
        return $this->customerProfile;
    }

    public function setCustomerProfile(CustomerProfile $customerProfile)
    {
        $this->customerProfile = $customerProfile;
    }

    /**
     * @return CustomerUser
     */
    public function getCustomerUser()
    {
        return $this->customerUser;
    }

    /**
     * @param CustomerUser $customerUser
     * @return $this
     */
    public function setCustomerUser(CustomerUser $customerUser = null)
    {
        $this->customerUser = $customerUser;

        return $this;
    }
}
