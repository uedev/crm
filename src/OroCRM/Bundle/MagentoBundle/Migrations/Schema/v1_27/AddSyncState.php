<?php

namespace OroCRM\Bundle\MagentoBundle\Migrations\Schema\v1_27;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class AddSyncState implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $customerTable = $schema->getTable('orocrm_magento_customer');
        $customerTable->addColumn('sync_state', 'integer', ['notnull' => false]);

        $orderTable = $schema->getTable('orocrm_magento_order');
        $orderTable->addColumn('sync_state', 'integer', ['notnull' => false]);

        $transportTable = $schema->getTable('oro_integration_transport');
        $transportTable->addColumn('initial_sync_start_date', 'datetime', ['notnull' => false]);
    }
}
