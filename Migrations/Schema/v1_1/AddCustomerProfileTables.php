<?php

namespace Oro\Bundle\AuthorizeNetBundle\Migrations\Schema\v1_1;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class AddCustomerProfileTables implements Migration
{
    const CUSTOMER_PROFILE_TABLE = 'oro_au_net_customer_profile';
    const CUSTOMER_PAYMENT_PROFILE_TABLE = 'oro_au_net_payment_profile';

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->createOroAuthorizeNetCustomerProfileTable($schema);
        $this->createOroAuthorizeNetCustomerPaymentProfileTable($schema);
        $this->addOroAuthorizeNetCustomerProfileForeignKeys($schema);
        $this->addOroAuthorizeNetCustomerPaymentProfileForeignKeys($schema);
    }

    protected function createOroAuthorizeNetCustomerProfileTable(Schema $schema)
    {
        $table = $schema->createTable(self::CUSTOMER_PROFILE_TABLE);
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('customer_profile_id', 'string', ['length' => 32]);
        $table->addColumn('integration_id', 'integer', ['notnull' => true]);
        $table->addColumn('customer_user_id', 'integer', ['notnull' => true]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
    }

    protected function createOroAuthorizeNetCustomerPaymentProfileTable(Schema $schema)
    {
        $table = $schema->createTable(self::CUSTOMER_PAYMENT_PROFILE_TABLE);
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

    protected function addOroAuthorizeNetCustomerProfileForeignKeys(Schema $schema)
    {
        $table = $schema->getTable(self::CUSTOMER_PROFILE_TABLE);
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

    protected function addOroAuthorizeNetCustomerPaymentProfileForeignKeys(Schema $schema)
    {
        $table = $schema->getTable(self::CUSTOMER_PAYMENT_PROFILE_TABLE);
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
            $schema->getTable(self::CUSTOMER_PROFILE_TABLE),
            ['customer_profile_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
    }
}
