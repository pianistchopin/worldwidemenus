<?php
/**
 * Tables settings
 */
$table_request_item 	= 'qquoteadv/requestitem';
$column_cost_price	= 'cost_price';
$comment_cost_price   = 'Cost Price';

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
            'default' => '0.0000',
            'comment' => $comment_cost_price
        )
    );
}
//}else {
//    $message = 'Error adding column '.$column_cost_price.' to table '.$installer->getTable($table_request_item).': Column already exist';
//    Mage::log('Upgrade Error: ' .$message, null, 'c2q_exception.log', true);
//}

$installer->endSetup();
