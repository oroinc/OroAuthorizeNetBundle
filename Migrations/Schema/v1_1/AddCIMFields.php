<?php

namespace Oro\Bundle\AuthorizeNetBundle\Migrations\Schema\v1_1;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class AddCIMFields implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->addEnabledFieldToIntegrationTransport($schema);
        $this->createOroAuthorizeNetEnabledCimWebsiteTable($schema);
        $this->addOroAuthorizeNetEnabledCimWebsiteForeignKeys($schema);
    }

    protected function addEnabledFieldToIntegrationTransport(Schema $schema)
    {
        $table = $schema->getTable('oro_integration_transport');
        $table->addColumn('au_net_enabled_cim', 'boolean', ['default' => '0', 'notnull' => false]);
    }

    protected function createOroAuthorizeNetEnabledCimWebsiteTable(Schema $schema)
    {
        $table = $schema->createTable('oro_au_net_enabled_cim_website');
        $table->addColumn('transport_id', 'integer', []);
        $table->addColumn('website_id', 'integer', []);
        $table->setPrimaryKey(['transport_id', 'website_id']);
    }

    /**
     * Add oro_au_net_enabled_cim_website foreign keys.
     */
    protected function addOroAuthorizeNetEnabledCimWebsiteForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('oro_au_net_enabled_cim_website');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_integration_transport'),
            ['transport_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_website'),
            ['website_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }
}
