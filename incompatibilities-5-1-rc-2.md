- [AuthorizeNetBundle](#authorizenetbundle)

AuthorizeNetBundle
------------------
* The following classes were removed:
   - `ExtendCustomerPaymentProfile`<sup>[[?]](https://github.com/oroinc/OroAuthorizeNetBundle/tree/5.0.0/Model/ExtendCustomerPaymentProfile.php#L8 "Oro\Bundle\AuthorizeNetBundle\Model\ExtendCustomerPaymentProfile")</sup>
   - `ExtendCustomerProfile`<sup>[[?]](https://github.com/oroinc/OroAuthorizeNetBundle/tree/5.0.0/Model/ExtendCustomerProfile.php#L8 "Oro\Bundle\AuthorizeNetBundle\Model\ExtendCustomerProfile")</sup>
* The `OroAuthorizeNetBundle::getContainerExtension`<sup>[[?]](https://github.com/oroinc/OroAuthorizeNetBundle/tree/5.0.0/OroAuthorizeNetBundle.php#L16 "Oro\Bundle\AuthorizeNetBundle\OroAuthorizeNetBundle::getContainerExtension")</sup> method was removed.
* The `OroAuthorizeNetExtension::getAlias`<sup>[[?]](https://github.com/oroinc/OroAuthorizeNetBundle/tree/5.0.0/DependencyInjection/OroAuthorizeNetExtension.php#L30 "Oro\Bundle\AuthorizeNetBundle\DependencyInjection\OroAuthorizeNetExtension::getAlias")</sup> method was removed.
* The `DisableCIMWithoutWebsites::preRemove(Website $website, LifecycleEventArgs $event)`<sup>[[?]](https://github.com/oroinc/OroAuthorizeNetBundle/tree/5.0.0/EventListener/DisableCIMWithoutWebsites.php#L17 "Oro\Bundle\AuthorizeNetBundle\EventListener\DisableCIMWithoutWebsites")</sup> method was changed to `DisableCIMWithoutWebsites::preRemove(Website $website, LifecycleEventArgs $event)`<sup>[[?]](https://github.com/oroinc/OroAuthorizeNetBundle/tree/5.1.0-rc.2/EventListener/DisableCIMWithoutWebsites.php#L17 "Oro\Bundle\AuthorizeNetBundle\EventListener\DisableCIMWithoutWebsites")</sup>

