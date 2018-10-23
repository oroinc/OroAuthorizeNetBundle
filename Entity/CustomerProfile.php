<?php

namespace Oro\Bundle\AuthorizeNetBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\AuthorizeNetBundle\Model\ExtendCustomerProfile;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Oro\Bundle\OrganizationBundle\Entity\OrganizationAwareInterface;
use Oro\Bundle\OrganizationBundle\Entity\Ownership\OrganizationAwareTrait;

/**
 * CustomerProfile Entity (Authorize.Net customerProfile)
 * @ORM\Table(name="oro_au_net_customer_profile")
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
class CustomerProfile extends ExtendCustomerProfile implements OrganizationAwareInterface
{
    use OrganizationAwareTrait;

    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(name="customer_profile_id", length=32, type="string")
     */
    protected $customerProfileId;

    /**
     * @var Integration
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\IntegrationBundle\Entity\Channel")
     * @ORM\JoinColumn(name="integration_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    protected $integration;

    /**
     * @var CustomerUser
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\CustomerBundle\Entity\CustomerUser")
     * @ORM\JoinColumn(name="customer_user_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    protected $customerUser;

    /**
     * @var ArrayCollection|CustomerPaymentProfile[]
     * @ORM\OneToMany(
     *     targetEntity="Oro\Bundle\AuthorizeNetBundle\Entity\CustomerPaymentProfile",
     *     mappedBy="customerProfile",
     *     cascade={"persist", "remove"},
     *     orphanRemoval=true
     * )
     */
    protected $paymentProfiles;

    public function __construct()
    {
        $this->paymentProfiles = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getCustomerProfileId()
    {
        return $this->customerProfileId;
    }

    /**
     * @param string $customerProfileId
     * @return $this
     */
    public function setCustomerProfileId($customerProfileId)
    {
        $this->customerProfileId = $customerProfileId;

        return $this;
    }

    /**
     * @return Integration
     */
    public function getIntegration()
    {
        return $this->integration;
    }

    /**
     * @param Integration $integration
     * @return $this
     */
    public function setIntegration(Integration $integration)
    {
        $this->integration = $integration;

        return $this;
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

    /**
     * @return ArrayCollection|CustomerPaymentProfile[]
     */
    public function getPaymentProfiles()
    {
        return $this->paymentProfiles;
    }

    /**
     * @param string $type
     * @return ArrayCollection|CustomerPaymentProfile[]
     */
    public function getPaymentProfilesByType($type)
    {
        return $this->paymentProfiles->filter(
            function (CustomerPaymentProfile $paymentProfile) use ($type) {
                return $type === $paymentProfile->getType();
            }
        );
    }

    /**
     * @param CustomerPaymentProfile $paymentProfile
     * @return $this
     */
    public function addPaymentProfile(CustomerPaymentProfile $paymentProfile)
    {
        if (!$this->paymentProfiles->contains($paymentProfile)) {
            $paymentProfile->setCustomerProfile($this);
            $this->paymentProfiles->add($paymentProfile);
        }

        return $this;
    }

    /**
     * @param CustomerPaymentProfile $paymentProfile
     * @return $this
     */
    public function removePaymentProfile(CustomerPaymentProfile $paymentProfile)
    {
        if ($this->paymentProfiles->contains($paymentProfile)) {
            $this->paymentProfiles->removeElement($paymentProfile);
        }

        return $this;
    }
}
