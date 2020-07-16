<?php
/**
 * Tables settings
 */
$table_request_item 	= 'qquoteadv/requestitem';
$column_cost_price	    = 'cost_price';
$comment_cost_price     = 'Cost Price';

/** @var Ophirah_Qquoteadv_Model_Mysql4_Setup $this */
$installer = $this;
$installer->startSetup();

/**
 * Check if column exists and if it doesn't add a new column.
 */
$tableName = $installer->getTable($table_request_item);
if (!$installer->getConnection()->tableColumnExists($tableName, $column_cost_price)) {
    $installer->getConnection()->addColumn(
        $tableName,
        $column_cost_price,
        array(
            'type' => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'scale' => 4,
            'precision' => 12,
            'default' => NULL,
            'comment' => $comment_cost_price
        )
    );
} else {
    //Fix cost_price from 5.0.6 from default 0.0000 to NULL
    $installer->run("ALTER TABLE `{$installer->getTable($table_request_item)}` CHANGE `$column_cost_price` `$column_cost_price` decimal(12,4) DEFAULT NULL;");
}

$installer->endSetup();