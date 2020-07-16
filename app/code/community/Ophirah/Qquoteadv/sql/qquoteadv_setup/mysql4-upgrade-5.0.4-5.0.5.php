<?php
/**
 * Tables settings
 */
$table_qqadvcustomer = 'qquoteadv/qqadvcustomer';

$column_subtotal = 'subtotal_incl_tax';
$column_base_subtotal = 'base_subtotal_incl_tax';
$comment_subtotal = 'Subtotal incl. tax';
$comment_base_subtotal = 'Base Subtotal incl tax';

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
    //on new installations, this field already exists and therefore an error message is not necessary
    //$message = 'Error adding column '.$column_subtotal.' to table '.$installer->getTable($table_qqadvcustomer).': Column already exist';
    //Mage::log('Upgrade Error: ' .$message, null, 'c2q_exception.log', true);
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
    //on new installations, this field already exists and therefore an error message is not necessary
    //$message = 'Error adding column '.$column_base_subtotal.' to table '.$installer->getTable($table_qqadvcustomer).': Column already exist';
    //Mage::log('Upgrade Error: ' .$message, null, 'c2q_exception.log', true);
}

$installer->endSetup();
