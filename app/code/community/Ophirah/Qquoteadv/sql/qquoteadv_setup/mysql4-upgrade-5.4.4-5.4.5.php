<?php
/**
 * Tables settings
 */
$table_qqadvaddress = 'quoteadv_quote_address';

$columnAppliedTaxes = 'applied_taxes';
$columnAppliedTaxesComment = 'Applied Taxes';

/** @var Ophirah_Qquoteadv_Model_Mysql4_Setup $this */
$installer = $this;
$installer->startSetup();

/**
 * Check if column exists and if it doesn't add a new column.
 */
$tableName = $installer->getTable($table_qqadvaddress);
if (!$installer->getConnection()->tableColumnExists($tableName, $columnAppliedTaxes)) {
    $installer->getConnection()
        ->addColumn(
            $tableName, $columnAppliedTaxes, array(
            'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length' => '64k',
            'comment' => $columnAppliedTaxesComment
        ));
} else {
    //on new installations, this field already exists and therefore an error message is not necessary
    //$message = 'Error adding column '.$column_subtotal.' to table '.$installer->getTable($table_qqadvaddress).': Column already exist';
    //Mage::log('Upgrade Error: ' .$message, null, 'c2q_exception.log', true);
}

$installer->endSetup();
