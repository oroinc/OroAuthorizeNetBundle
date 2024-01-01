<?php

namespace Oro\Bundle\AuthorizeNetBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 */
class OroAuthorizeNetBundleInstaller implements Installation
{
    /**
     * {@inheritDoc}
     */
    public function getMigrationVersion(): string
    {
        return 'v1_2';
    }

    /**
     * {@inheritDoc}
     */
    public function up(Schema $schema, QueryBag $queries): void
    {
        $this->updateOroIntegrationTransportTable($schema);

        $this->createOroAuthorizeNetCreditCardLblTable($schema);
        $this->createOroAuthorizeNetCreditCardShLblTable($schema);
        $this->createOroAuthorizeNetEnabledCimWebsiteTable($schema);
        $this->createOroAuthorizeNetCustomerProfileTable($schema);
        $this->createOroAuthorizeNetCustomerPaymentProfileTable($schema);
        $this->createOroAuthorizeNetECheckLabelTable($schema);
        $this->createOroAuthorizeNetECheckShortLabelTable($schema);

        $this->addOroAuthorizeNetCreditCardLblForeignKeys($schema);
        $this->addOroAuthorizeNetCreditCardShLblForeignKeys($schema);
        $this->addOroAuthorizeNetEnabledCimWebsiteForeignKeys($schema);
        $this->addOroAuthorizeNetCustomerProfileForeignKeys($schema);
        $this->addOroAuthorizeNetCustomerPaymentProfileForeignKeys($schema);
        $this->addOroAuthorizeNetECheckLabelForeignKeys($schema);
        $this->addOroAuthorizeNetECheckShortLabelForeignKeys($schema);
    }

    /**
     * Update oro_integration_transport table
     */
    private function updateOroIntegrationTransportTable(Schema $schema): void
    {
        $table = $schema->getTable('oro_integration_transport');
        $table->addColumn('au_net_api_login', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('au_net_transaction_key', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('au_net_client_key', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('au_net_credit_card_action', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('au_net_allowed_card_types', 'array', ['notnull' => false, 'comment' => '(DC2Type:array)']);
        $table->addColumn('au_net_test_mode', 'boolean', ['default' => false, 'notnull' => false]);
        $table->addColumn('au_net_require_cvv_entry', 'boolean', ['default' => true, 'notnull' => false]);
        $table->addColumn('au_net_enabled_cim', 'boolean', ['default' => false, 'notnull' => false]);
        $table->addColumn('au_net_allow_hold_transaction', 'boolean', ['default' => true, 'notnull' => false]);
        $table->addColumn('au_net_echeck_enabled', 'boolean', ['default' => false, 'notnull' => false]);
        $table->addColumn('au_net_echeck_account_types', 'array', ['notnull' => false, 'comment' => '(DC2Type:array)']);
        $table->addColumn('au_net_echeck_confirmation_txt', 'text', ['notnull' => false]);
    }

    /**
     * Create oro_au_net_credit_card_lbl table
     */
    private function createOroAuthorizeNetCreditCardLblTable(Schema $schema): void
    {
        $table = $schema->createTable('oro_au_net_credit_card_lbl');
        $table->addColumn('transport_id', 'integer');
        $table->addColumn('localized_value_id', 'integer');
        $table->setPrimaryKey(['transport_id', 'localized_value_id']);
        $table->addUniqueIndex(['localized_value_id']);
    }

    /**
     * Create oro_au_net_credit_card_sh_lbl table
     */
    private function createOroAuthorizeNetCreditCardShLblTable(Schema $schema): void
    {
        $table = $schema->createTable('oro_au_net_credit_card_sh_lbl');
        $table->addColumn('transport_id', 'integer');
        $table->addColumn('localized_value_id', 'integer');
        $table->setPrimaryKey(['transport_id', 'localized_value_id']);
        $table->addUniqueIndex(['localized_value_id']);
    }

    /**
     * Create oro_au_net_enabled_cim_website
     */
    private function createOroAuthorizeNetEnabledCimWebsiteTable(Schema $schema): void
    {
        $table = $schema->createTable('oro_au_net_enabled_cim_website');
        $table->addColumn('transport_id', 'integer');
        $table->addColumn('website_id', 'integer');
        $table->setPrimaryKey(['transport_id', 'website_id']);
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

    /**
     * Add oro_au_net_credit_card_lbl foreign keys.
     */
    private function addOroAuthorizeNetCreditCardLblForeignKeys(Schema $schema): void
    {
        $table = $schema->getTable('oro_au_net_credit_card_lbl');
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

    /**
     * Add oro_au_net_credit_card_sh_lbl foreign keys.
     */
    private function addOroAuthorizeNetCreditCardShLblForeignKeys(Schema $schema): void
    {
        $table = $schema->getTable('oro_au_net_credit_card_sh_lbl');
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

    /**
     * Add oro_au_net_enabled_cim_website foreign keys.
     */
    private function addOroAuthorizeNetEnabledCimWebsiteForeignKeys(Schema $schema): void
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
