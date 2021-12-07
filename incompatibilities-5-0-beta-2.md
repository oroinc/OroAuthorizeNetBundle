AuthorizeNetBundle
------------------
* The `PaymentProfileAddressDTO::setFirstName($firstName)`<sup>[[?]](https://github.com/oroinc/OroAuthorizeNetBundle/tree/5.0.0-beta.1/Model/DTO/PaymentProfileAddressDTO.php#L58 "Oro\Bundle\AuthorizeNetBundle\Model\DTO\PaymentProfileAddressDTO")</sup> method was changed to `PaymentProfileAddressDTO::setFirstName($firstName)`<sup>[[?]](https://github.com/oroinc/OroAuthorizeNetBundle/tree/5.0.0-beta.2/Model/DTO/PaymentProfileAddressDTO.php#L40 "Oro\Bundle\AuthorizeNetBundle\Model\DTO\PaymentProfileAddressDTO")</sup>
* The `CustomerProfileAndCustomerPaymentProfileVoter::__construct(DoctrineHelper $doctrineHelper, TokenAccessorInterface $tokenAccessor)`<sup>[[?]](https://github.com/oroinc/OroAuthorizeNetBundle/tree/5.0.0-beta.1/Acl/Voter/CustomerProfileAndCustomerPaymentProfileVoter.php#L22 "Oro\Bundle\AuthorizeNetBundle\Acl\Voter\CustomerProfileAndCustomerPaymentProfileVoter")</sup> method was changed to `CustomerProfileAndCustomerPaymentProfileVoter::__construct(DoctrineHelper $doctrineHelper, $className)`<sup>[[?]](https://github.com/oroinc/OroAuthorizeNetBundle/tree/5.0.0-beta.2/Acl/Voter/CustomerProfileAndCustomerPaymentProfileVoter.php#L22 "Oro\Bundle\AuthorizeNetBundle\Acl\Voter\CustomerProfileAndCustomerPaymentProfileVoter")</sup>
* The `ResponseFactory::__construct(ArrayTransformerInterface $serializer)`<sup>[[?]](https://github.com/oroinc/OroAuthorizeNetBundle/tree/5.0.0-beta.1/AuthorizeNet/Response/ResponseFactory.php#L18 "Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Response\ResponseFactory")</sup> method was changed to `ResponseFactory::__construct(ArrayTransformerInterface $serializer = null)`<sup>[[?]](https://github.com/oroinc/OroAuthorizeNetBundle/tree/5.0.0-beta.2/AuthorizeNet/Response/ResponseFactory.php#L16 "Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Response\ResponseFactory")</sup>
* The following methods in class `CustomerProfileAndCustomerPaymentProfileVoter`<sup>[[?]](https://github.com/oroinc/OroAuthorizeNetBundle/tree/5.0.0-beta.1/Acl/Voter/CustomerProfileAndCustomerPaymentProfileVoter.php#L31 "Oro\Bundle\AuthorizeNetBundle\Acl\Voter\CustomerProfileAndCustomerPaymentProfileVoter")</sup> were removed:
   - `supportsAttribute`<sup>[[?]](https://github.com/oroinc/OroAuthorizeNetBundle/tree/5.0.0-beta.1/Acl/Voter/CustomerProfileAndCustomerPaymentProfileVoter.php#L31 "Oro\Bundle\AuthorizeNetBundle\Acl\Voter\CustomerProfileAndCustomerPaymentProfileVoter::supportsAttribute")</sup>
   - `supportsAttributes`<sup>[[?]](https://github.com/oroinc/OroAuthorizeNetBundle/tree/5.0.0-beta.1/Acl/Voter/CustomerProfileAndCustomerPaymentProfileVoter.php#L39 "Oro\Bundle\AuthorizeNetBundle\Acl\Voter\CustomerProfileAndCustomerPaymentProfileVoter::supportsAttributes")</sup>
   - `getPermissionForAttribute`<sup>[[?]](https://github.com/oroinc/OroAuthorizeNetBundle/tree/5.0.0-beta.1/Acl/Voter/CustomerProfileAndCustomerPaymentProfileVoter.php#L59 "Oro\Bundle\AuthorizeNetBundle\Acl\Voter\CustomerProfileAndCustomerPaymentProfileVoter::getPermissionForAttribute")</sup>