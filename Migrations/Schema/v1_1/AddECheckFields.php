<?php

namespace Oro\Bundle\AuthorizeNetBundle\Migrations\Schema\v1_1;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * eCheck settings fields
 */
class AddECheckFields implements Migration
{
    /**
     * {@inheritDoc}
     */
    public function up(Schema $schema, QueryBag $queries): void
    {
        $this->updateOroIntegrationTransportTable($schema);
        $this->createOroAuthorizeNetECheckLabelTable($schema);
        $this->createOroAuthorizeNetECheckShortLabelTable($schema);
        $this->addOroAuthorizeNetECheckLabelForeignKeys($schema);
        $this->addOroAuthorizeNetECheckShortLabelForeignKeys($schema);
    }

    private function updateOroIntegrationTransportTable(Schema $schema): void
    {
        $table = $schema->getTable('oro_integration_transport');
        $table->addColumn('au_net_echeck_enabled', 'boolean', ['default' => '0', 'notnull' => false]);
        $table->addColumn('au_net_echeck_account_types', 'array', ['notnull' => false, 'comment' => '(DC2Type:array)']);
        $table->addColumn('au_net_echeck_confirmation_txt', 'text', ['notnull' => false]);
    }

    private function createOroAuthorizeNetECheckLabelTable(Schema $schema): void
    {
        $table = $schema->createTable('oro_au_net_echeck_label');
        $table->addColumn('transport_id', 'integer');
        $table->addColumn('localized_value_id', 'integer');
        $table->setPrimaryKey(['transport_id', 'localized_value_id']);
        $table->addUniqueIndex(['localized_value_id']);
    }

    private function createOroAuthorizeNetECheckShortLabelTable(Schema $schema): void
    {
        $table = $schema->createTable('oro_au_net_echeck_short_label');
        $table->addColumn('transport_id', 'integer');
        $table->addColumn('localized_value_id', 'integer');
        $table->setPrimaryKey(['transport_id', 'localized_value_id']);
        $table->addUniqueIndex(['localized_value_id']);
    }

    private function addOroAuthorizeNetECheckLabelForeignKeys(Schema $schema): void
    {
        $table = $schema->getTable('oro_au_net_echeck_label');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_integration_transport'),
            ['transport_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_fallback_localization_val'),
            ['localized_value_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }

    private function addOroAuthorizeNetECheckShortLabelForeignKeys(Schema $schema): void
    {
        $table = $schema->getTable('oro_au_net_echeck_short_label');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_integration_transport'),
            ['transport_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_fallback_localization_val'),
            ['localized_value_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }
}
