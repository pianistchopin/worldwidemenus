<?php
/**
 * Tables settings
 */
$table_qqadvaddress = 'quoteadv_quote_address';
$newColumns = array(
    "org_final_base_price_incl_tax" => "Original final price including tax"
);

/** @var Ophirah_Qquoteadv_Model_Mysql4_Setup $this */
$installer = $this;
$installer->startSetup();

/**
 * Check if column exists and if it doesn't add a new column.
 */
$tableName = $installer->getTable($table_qqadvaddress);
foreach($newColumns as $column => $comment) {
    if (!$installer->getConnection()->tableColumnExists($tableName, $column)) {
        $installer->getConnection()
            ->addColumn(
                $tableName, $column, array(
                'type' => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale' => 4,
                'precision' => 12,
                'default' => '0.0000',
                'comment' => $comment
            ));
    } else {
        //on new installations, this field already exists and therefore an error message is not necessary
        //$message = 'Error adding column '.$column_subtotal.' to table '.$installer->getTable($table_qqadvaddress).': Column already exist';
        //Mage::log('Upgrade Error: ' .$message, null, 'c2q_exception.log', true);
    }
}
$installer->endSetup();
