Please refer first to [UPGRADE.md](UPGRADE.md) for the most important items that should be addressed before attempting to upgrade or during the upgrade of a vanilla Oro application.

The current file describes significant changes in the code that may affect the upgrade of your customizations.

## 4.1.1 (2020-02-25)
[Show detailed list of changes](incompatibilities-4-1-1.md)

## 4.1.0 (2020-01-31)

### Removed
* `*.class` parameters for all entities were removed from the dependency injection container.
The entity class names should be used directly, e.g. `'Oro\Bundle\EmailBundle\Entity\Email'`
instead of `'%oro_email.email.entity.class%'` (in service definitions, datagrid config files, placeholders, etc.), and
`\Oro\Bundle\EmailBundle\Entity\Email::class` instead of `$container->getParameter('oro_email.email.entity.class')`
(in PHP code).

## 4.0.0 (2019-07-31)
[Show detailed list of changes](incompatibilities-4-0.md)

## 3.1.0 (2019-01-30)
[Show detailed list of changes](incompatibilities-3-1.md)
