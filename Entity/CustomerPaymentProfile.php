<?php

namespace Oro\Bundle\AuthorizeNetBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Extend\Entity\Autocomplete\OroAuthorizeNetBundle_Entity_CustomerPaymentProfile;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\Config;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityTrait;
use Oro\Bundle\OrganizationBundle\Entity\OrganizationAwareInterface;
use Oro\Bundle\OrganizationBundle\Entity\Ownership\OrganizationAwareTrait;

/**
 * Entity that consist information "customerPaymentProfileId" from Authorize.Net
 * and binds it to Oro CustomerProfile entity
 * @mixin OroAuthorizeNetBundle_Entity_CustomerPaymentProfile
 */
#[ORM\Entity]
#[ORM\Table(name: 'oro_au_net_payment_profile')]
#[ORM\Index(columns: ['name'], name: 'oro_au_net_payment_profile_name_idx')]
#[ORM\Index(columns: ['type'], name: 'oro_au_net_payment_profile_type_idx')]
#[Config(
    mode: 'hidden',
    defaultValues: [
        'ownership' => [
            'owner_type' => 'ORGANIZATION',
            'owner_field_name' => 'organization',
            'owner_column_name' => 'organization_id',
            'frontend_owner_type' => 'FRONTEND_USER',
            'frontend_owner_field_name' => 'customerUser',
            'frontend_owner_column_name' => 'customer_user_id'
        ],
        'security' => ['type' => 'ACL', 'group_name' => 'commerce']
    ]
)]
class CustomerPaymentProfile implements OrganizationAwareInterface, ExtendEntityInterface
{
    use OrganizationAwareTrait;
    use ExtendEntityTrait;

    public const TYPE_CREDITCARD = 'creditcard';
    public const TYPE_ECHECK = 'echeck';

    public const ALLOWED_TYPES = [
        self::TYPE_CREDITCARD,
        self::TYPE_ECHECK
    ];

    #[ORM\Id]
    #[ORM\Column(type: Types::INTEGER)]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected ?int $id = null;

    #[ORM\Column(name: 'type', type: Types::STRING, length: 10, options: ['default' => 'creditcard'])]
    protected ?string $type = self::TYPE_CREDITCARD;

    #[ORM\Column(name: 'name', type: Types::STRING, length: 25)]
    protected ?string $name = null;

    #[ORM\Column(name: 'last_digits', type: Types::STRING, length: 4)]
    protected ?string $lastDigits = null;

    #[ORM\Column(name: 'is_default', type: Types::BOOLEAN, options: ['default' => false])]
    protected ?bool $default = false;

    #[ORM\Column(name: 'customer_payment_profile_id', type: Types::STRING, length: 32)]
    protected ?string $customerPaymentProfileId = null;

    #[ORM\ManyToOne(targetEntity: CustomerProfile::class, inversedBy: 'paymentProfiles')]
    #[ORM\JoinColumn(name: 'customer_profile_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    protected ?CustomerProfile $customerProfile = null;

    #[ORM\ManyToOne(targetEntity: CustomerUser::class)]
    #[ORM\JoinColumn(name: 'customer_user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    protected ?CustomerUser $customerUser = null;

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
     * @param CustomerUser|null $customerUser
     * @return $this
     */
    public function setCustomerUser(?CustomerUser $customerUser = null)
    {
        $this->customerUser = $customerUser;

        return $this;
    }
}
