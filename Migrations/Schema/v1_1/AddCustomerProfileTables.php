<?php

namespace Oro\Bundle\AuthorizeNetBundle\Migrations\Schema\v1_1;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class AddCustomerProfileTables implements Migration
{
    #[\Override]
    public function up(Schema $schema, QueryBag $queries): void
    {
        $this->createOroAuthorizeNetCustomerProfileTable($schema);
        $this->createOroAuthorizeNetCustomerPaymentProfileTable($schema);
        $this->addOroAuthorizeNetCustomerProfileForeignKeys($schema);
        $this->addOroAuthorizeNetCustomerPaymentProfileForeignKeys($schema);
    }

    private function createOroAuthorizeNetCustomerProfileTable(Schema $schema): void
    {
        $table = $schema->createTable('oro_au_net_customer_profile');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('customer_profile_id', 'string', ['length' => 32]);
        $table->addColumn('integration_id', 'integer', ['notnull' => true]);
        $table->addColumn('customer_user_id', 'integer', ['notnull' => true]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
    }

    private function createOroAuthorizeNetCustomerPaymentProfileTable(Schema $schema): void
    {
        $table = $schema->createTable('oro_au_net_payment_profile');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('type', 'string', ['length' => 10, 'default' => 'creditcard']);
        $table->addColumn('name', 'string', ['length' => 25]);
        $table->addColumn('last_digits', 'string', ['length' => 4]);
        $table->addColumn('is_default', 'boolean', ['default' => false]);
        $table->addColumn('customer_payment_profile_id', 'string', ['length' => 32]);
        $table->addColumn('customer_profile_id', 'integer', ['notnull' => true]);
        $table->addColumn('customer_user_id', 'integer', ['notnull' => true]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['name'], 'oro_au_net_payment_profile_name_idx');
        $table->addIndex(['type'], 'oro_au_net_payment_profile_type_idx');
    }

    private function addOroAuthorizeNetCustomerProfileForeignKeys(Schema $schema): void
    {
        $table = $schema->getTable('oro_au_net_customer_profile');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['organization_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'SET NULL']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_customer_user'),
            ['customer_user_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_integration_channel'),
            ['integration_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
    }

    private function addOroAuthorizeNetCustomerPaymentProfileForeignKeys(Schema $schema): void
    {
        $table = $schema->getTable('oro_au_net_payment_profile');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['organization_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'SET NULL']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_customer_user'),
            ['customer_user_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_au_net_customer_profile'),
            ['customer_profile_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
    }
}
