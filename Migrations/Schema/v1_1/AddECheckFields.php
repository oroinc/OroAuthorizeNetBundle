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
    const INTEGRATION_TRANSPORT_TABLE = 'oro_integration_transport';
    const FALLBACK_LOCALIZATION_VALUE_TABLE = 'oro_fallback_localization_val';
    const ECHECK_ENABLED_COLUMN = 'au_net_echeck_enabled';
    const ECHECK_ACCOUNT_TYPES_COLUMN = 'au_net_echeck_account_types';
    const ECHECK_CONFIRMATION_TEXT_COLUMN = 'au_net_echeck_confirmation_txt';
    const ECHECK_LABEL_TABLE = 'oro_au_net_echeck_label';
    const ECHECK_SHORT_LABEL_TABLE = 'oro_au_net_echeck_short_label';

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->updateOroIntegrationTransportTable($schema);
        $this->createOroAuthorizeNetECheckLabelTable($schema);
        $this->createOroAuthorizeNetECheckShortLabelTable($schema);
        $this->addOroAuthorizeNetECheckLabelForeignKeys($schema);
        $this->addOroAuthorizeNetECheckShortLabelForeignKeys($schema);
    }

    /**
     * @param Schema $schema
     */
    protected function updateOroIntegrationTransportTable(Schema $schema)
    {
        $table = $schema->getTable(self::INTEGRATION_TRANSPORT_TABLE);

        $table->addColumn(self::ECHECK_ENABLED_COLUMN, 'boolean', [
            'default' => '0',
            'notnull' => false
        ]);
        $table->addColumn(self::ECHECK_ACCOUNT_TYPES_COLUMN, 'array', [
            'notnull' => false,
            'comment' => '(DC2Type:array)'
        ]);
        $table->addColumn(self::ECHECK_CONFIRMATION_TEXT_COLUMN, 'text', [
            'notnull' => false
        ]);
    }

    /**
     * @param Schema $schema
     */
    protected function createOroAuthorizeNetECheckLabelTable(Schema $schema)
    {
        $table = $schema->createTable(self::ECHECK_LABEL_TABLE);
        $table->addColumn('transport_id', 'integer');
        $table->addColumn('localized_value_id', 'integer');
        $table->setPrimaryKey(['transport_id', 'localized_value_id']);
        $table->addUniqueIndex(['localized_value_id']);
    }

    /**
     * @param Schema $schema
     */
    protected function createOroAuthorizeNetECheckShortLabelTable(Schema $schema)
    {
        $table = $schema->createTable(self::ECHECK_SHORT_LABEL_TABLE);
        $table->addColumn('transport_id', 'integer');
        $table->addColumn('localized_value_id', 'integer');
        $table->setPrimaryKey(['transport_id', 'localized_value_id']);
        $table->addUniqueIndex(['localized_value_id']);
    }

    /**
     * @param Schema $schema
     */
    protected function addOroAuthorizeNetECheckLabelForeignKeys(Schema $schema)
    {
        $table = $schema->getTable(self::ECHECK_LABEL_TABLE);
        $table->addForeignKeyConstraint(
            $schema->getTable(self::INTEGRATION_TRANSPORT_TABLE),
            ['transport_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable(self::FALLBACK_LOCALIZATION_VALUE_TABLE),
            ['localized_value_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }

    /**
     * @param Schema $schema
     */
    protected function addOroAuthorizeNetECheckShortLabelForeignKeys(Schema $schema)
    {
        $table = $schema->getTable(self::ECHECK_SHORT_LABEL_TABLE);
        $table->addForeignKeyConstraint(
            $schema->getTable(self::INTEGRATION_TRANSPORT_TABLE),
            ['transport_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable(self::FALLBACK_LOCALIZATION_VALUE_TABLE),
            ['localized_value_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }
}
