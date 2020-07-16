<?php
/**
 * Tables settings
 */
$table_qqadvcustomer = 'qquoteadv/qqadvcustomer';

$column_subtotal = 'subtotal_incl_tax';
$column_base_subtotal = 'base_subtotal_incl_tax';
$comment_subtotal = 'Subtotal incl. tax';
$comment_base_subtotal = 'Base Subtotal incl tax';

$table_request_item 	= 'qquoteadv/requestitem';
$column_cost_price	    = 'cost_price';
$comment_cost_price     = 'Cost Price';

/** @var Ophirah_Qquoteadv_Model_Mysql4_Setup $this */
$installer = $this;
$installer->startSetup();

/**
 * Check if column exists and if it doesn't add a new column.
 */
$tableName = $installer->getTable($table_qqadvcustomer);
if (!$installer->getConnection()->tableColumnExists($tableName, $column_subtotal)) {
    $installer->getConnection()
        ->addColumn(
            $tableName, $column_subtotal, array(
            'type' => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'scale' => 4,
            'precision' => 12,
            'default' => '0.0000',
            'comment' => $comment_subtotal
        ));
} else {
    //Fix percision to precision
    $installer->run("ALTER TABLE `{$tableName}` CHANGE `$column_subtotal` `$column_subtotal` decimal(12,4) DEFAULT '0.0000';");
}

if (!$installer->getConnection()->tableColumnExists($tableName, $column_base_subtotal)) {
    $installer->getConnection()
        ->addColumn(
            $tableName, $column_base_subtotal, array(
            'type' => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'scale' => 4,
            'precision' => 12,
            'default' => '0.0000',
            'comment' => $column_base_subtotal
        ));
} else {
    //Fix percision to precision
    $installer->run("ALTER TABLE `{$tableName}` CHANGE `$column_base_subtotal` `$column_base_subtotal` decimal(12,4) DEFAULT '0.0000';");
}

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