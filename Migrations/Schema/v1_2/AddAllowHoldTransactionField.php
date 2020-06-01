<?php

namespace Oro\Bundle\AuthorizeNetBundle\Migrations\Schema\v1_2;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\AuthorizeNetBundle\Migrations\Schema\OroAuthorizeNetBundleInstaller;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * Added au_net_allow_hold_transaction field
 */
class AddAllowHoldTransactionField implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries): void
    {
        $table = $schema->getTable(OroAuthorizeNetBundleInstaller::INTEGRATION_TRANSPORT_TABLE);
        $table->addColumn('au_net_allow_hold_transaction', 'boolean', [
            'default' => true,
            'notnull' => false
        ]);
    }
}
