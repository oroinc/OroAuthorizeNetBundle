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
    const CUSTOMER_PROFILE_TABLE = 'oro_au_net_customer_profile';
    const CUSTOMER_PAYMENT_PROFILE_TABLE = 'oro_au_net_payment_profile';

    const INTEGRATION_TRANSPORT_TABLE = 'oro_integration_transport';
    const FALLBACK_LOCALIZATION_VALUE_TABLE = 'oro_fallback_localization_val';

    const ECHECK_ENABLED_COLUMN = 'au_net_echeck_enabled';
    const ECHECK_ACCOUNT_TYPES_COLUMN = 'au_net_echeck_account_types';
    const ECHECK_CONFIRMATION_TEXT_COLUMN = 'au_net_echeck_confirmation_txt';
    const ECHECK_LABEL_TABLE = 'oro_au_net_echeck_label';
    const ECHECK_SHORT_LABEL_TABLE = 'oro_au_net_echeck_short_label';

    /**
     * {@inheritDoc}
     */
    public function getMigrationVersion()
    {
        return 'v1_2';
    }

    /**
     * {@inheritDoc}
     */
    public function up(Schema $schema, QueryBag $queries)
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
     * @param Schema $schema
     */
    protected function updateOroIntegrationTransportTable(Schema $schema)
    {
        $table = $schema->getTable(self::INTEGRATION_TRANSPORT_TABLE);
        $table->addColumn('au_net_api_login', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('au_net_transaction_key', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('au_net_client_key', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('au_net_credit_card_action', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('au_net_allowed_card_types', 'array', ['notnull' => false, 'comment' => '(DC2Type:array)']);
        $table->addColumn('au_net_test_mode', 'boolean', ['default' => false, 'notnull' => false]);
        $table->addColumn('au_net_require_cvv_entry', 'boolean', ['default' => true, 'notnull' => false]);
        $table->addColumn('au_net_enabled_cim', 'boolean', ['default' => false, 'notnull' => false]);
        $table->addColumn('au_net_allow_hold_transaction', 'boolean', ['default' => true, 'notnull' => false]);

        $table->addColumn(self::ECHECK_ENABLED_COLUMN, 'boolean', [
            'default' => false,
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
     * Create oro_au_net_credit_card_lbl table
     * @param Schema $schema
     */
    protected function createOroAuthorizeNetCreditCardLblTable(Schema $schema)
    {
        $table = $schema->createTable('oro_au_net_credit_card_lbl');
        $table->addColumn('transport_id', 'integer', []);
        $table->addColumn('localized_value_id', 'integer', []);
        $table->setPrimaryKey(['transport_id', 'localized_value_id']);
        $table->addUniqueIndex(['localized_value_id']);
    }

    /**
     * Create oro_au_net_credit_card_sh_lbl table
     * @param Schema $schema
     */
    protected function createOroAuthorizeNetCreditCardShLblTable(Schema $schema)
    {
        $table = $schema->createTable('oro_au_net_credit_card_sh_lbl');
        $table->addColumn('transport_id', 'integer', []);
        $table->addColumn('localized_value_id', 'integer', []);
        $table->setPrimaryKey(['transport_id', 'localized_value_id']);
        $table->addUniqueIndex(['localized_value_id']);
    }

    /**
     * Create oro_au_net_enabled_cim_website
     * @param Schema $schema
     */
    protected function createOroAuthorizeNetEnabledCimWebsiteTable(Schema $schema)
    {
        $table = $schema->createTable('oro_au_net_enabled_cim_website');
        $table->addColumn('transport_id', 'integer', []);
        $table->addColumn('website_id', 'integer', []);
        $table->setPrimaryKey(['transport_id', 'website_id']);
    }

    /**
     * @param Schema $schema
     */
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

    /**
     * @param Schema $schema
     */
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

    /**
     * Add oro_au_net_credit_card_lbl foreign keys.
     * @param Schema $schema
     */
    protected function addOroAuthorizeNetCreditCardLblForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('oro_au_net_credit_card_lbl');
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
     * Add oro_au_net_credit_card_sh_lbl foreign keys.
     * @param Schema $schema
     */
    protected function addOroAuthorizeNetCreditCardShLblForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('oro_au_net_credit_card_sh_lbl');
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
     * Add oro_au_net_enabled_cim_website foreign keys.
     * @param Schema $schema
     */
    protected function addOroAuthorizeNetEnabledCimWebsiteForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('oro_au_net_enabled_cim_website');
        $table->addForeignKeyConstraint(
            $schema->getTable(self::INTEGRATION_TRANSPORT_TABLE),
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

    /**
     * @param Schema $schema
     */
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

    /**
     * @param Schema $schema
     */
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
