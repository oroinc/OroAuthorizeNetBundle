The upgrade instructions are available at [Oro documentation website](https://doc.oroinc.com/master/backend/setup/upgrade-to-new-version/).

The current file describes significant changes in the code that may affect the upgrade of your customizations.

## Changes in the Authorizenet package versions

- [6.1.0](#610-2025-03-31)
- [6.0.0](#600-2024-03-30)
- [5.1.0](#510-2023-03-31)
- [5.0.0](#500-2022-01-26)
- [4.2.0](#420-2020-01-29)
- [4.1.0](#410-2020-01-31)
- [4.0.0](#400-2019-07-31)
- [3.1.0](#310-2019-01-30)

## 6.1.0 (2025-03-31)
[Show detailed list of changes](incompatibilities-6-1.md)

## 6.0.0 (2024-03-30)
[Show detailed list of changes](incompatibilities-6-0.md)

## 5.1.0 (2023-03-31)
[Show detailed list of changes](incompatibilities-5-1.md)

## 5.0.0 (2022-01-26)
[Show detailed list of changes](incompatibilities-5-0.md)

## 4.2.0 (2020-01-29)
[Show detailed list of changes](incompatibilities-4-2.md)

### Changed
* The `getPriority()` method was removed from
  the `Oro\Bundle\AuthorizeNetBundle\AuthorizeNet\Client\RequestConfigurator\RequestConfiguratorInterface` interface.
  Use the `priority` attribute of the `oro_authorize_net.authorize_net.client.request_configurator` DIC tag
  to manage the order of request configurators.


## 4.1.0 (2020-01-31)

[Show detailed list of changes](incompatibilities-4-1.md)

### Removed
* The `*.class` parameters for all entities were removed from the dependency injection container.
The entity class names should be used directly, e.g., `'Oro\Bundle\EmailBundle\Entity\Email'`
instead of `'%oro_email.email.entity.class%'` (in service definitions, datagrid config files, placeholders, etc.), and
`\Oro\Bundle\EmailBundle\Entity\Email::class` instead of `$container->getParameter('oro_email.email.entity.class')`
(in PHP code).

## 4.0.0 (2019-07-31)
[Show detailed list of changes](incompatibilities-4-0.md)

## 3.1.0 (2019-01-30)
[Show detailed list of changes](incompatibilities-3-1.md)
