<?php

namespace Oro\Bundle\AuthorizeNetBundle\Migrations\Schema\v1_2;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * Added au_net_allow_hold_transaction field
 */
class AddAllowHoldTransactionField implements Migration
{
    /**
     * {@inheritDoc}
     */
    public function up(Schema $schema, QueryBag $queries): void
    {
        $schema->getTable('oro_integration_transport')
            ->addColumn('au_net_allow_hold_transaction', 'boolean', ['default' => true,'notnull' => false]);
    }
}
