<?php
/**
 *
 * CART2QUOTE CONFIDENTIAL
 * __________________
 *
 *  [2009] - [2020] Cart2Quote B.V.
 *  All Rights Reserved.
 *
 * NOTICE OF LICENSE
 *
 * All information contained herein is, and remains
 * the property of Cart2Quote B.V. and its suppliers,
 * if any.  The intellectual and technical concepts contained
 * herein are proprietary to Cart2Quote B.V.
 * and its suppliers and may be covered by European and Foreign Patents,
 * patents in process, and are protected by trade secret or copyright law.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Cart2Quote B.V.
 *
 * @category    Ophirah
 * @package     Qquoteadv
 * @copyright   Copyright (c) 2020 Cart2Quote B.V. (https://www.cart2quote.com)
 * @license     https://www.cart2quote.com/ordering-licenses(https://www.cart2quote.com)
 */

/**
 * Tables settings
 */
$table_qqadvaddress = 'quoteadv_quote_address';
$newColumns = array(
    "org_final_base_price" => "Original final price",
    "quote_final_base_price" => "Quote final base price",
    "quote_base_cost_price" => "Cost price from quote",
    "org_base_price" => "Original base price"
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
