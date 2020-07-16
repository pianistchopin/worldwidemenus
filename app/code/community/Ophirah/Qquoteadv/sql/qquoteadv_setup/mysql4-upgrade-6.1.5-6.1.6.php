<?php
/**
 * CART2QUOTE CONFIDENTIAL
 * __________________
 *  [2009] - [2020] Cart2Quote B.V.
 *  All Rights Reserved.
 * NOTICE OF LICENSE
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

/** @var Ophirah_Qquoteadv_Model_Mysql4_Setup $this */
$installer = $this;
$installer->startSetup();

//remove wrong group prices form v6.1.5 - cast decimal to int before compair
$groupPriceTable = $installer->getTable('catalog_product_entity_group_price');

$groupPriceCollection = Mage::getModel('qquoteadv/grouppriceinfo')->getCollection();
$groupPriceCollection->getSelect()
    ->joinLeft(
        array('cpegp' => $groupPriceTable),
        'cpegp.entity_id = main_table.product_id
            AND cpegp.customer_group_id = main_table.customer_group_id
            AND FLOOR(cpegp.value) = main_table.group_price'
    )
    ->where("cpegp.value_id IS NOT NULL")
    ->group('cpegp.value_id');

$groupPriceIds = $groupPriceCollection->getColumnValues('value_id');
if (!empty($groupPriceIds)) {
    $connection = $installer->getConnection();
    $connection->query(
        $connection->quoteInto(
            "DELETE FROM `{$groupPriceTable}` WHERE `value_id` IN (?)", $groupPriceIds
        )
    );
}

//fix price saved in int database, to decimal 12.4
$tablePriceInfo = 'quoteadv_group_price_info';
$columnGroupPrice = 'group_price';
$tableName = $installer->getTable($tablePriceInfo);
if ($installer->getConnection()->tableColumnExists($tableName, $columnGroupPrice)) {
    $installer->getConnection()
        ->changeColumn(
            $tableName,
            $columnGroupPrice,
            $columnGroupPrice,
            array(
                'type'      => 'decimal',
                'precision' => 12,
                'scale'     => 4
            )
        );
} else {
    $message = 'Error modifying column ' . $columnGroupPrice;
    $message .= ' to table ' . $installer->getTable($tablePriceInfo) . ': Column does not exist';
    Mage::log('Exception: ' . $message, null, 'c2q_exception.log', true);
}

//remove first_time in sales_flat_order_item
$tableFlatOrderItem = 'sales_flat_order_item';
$columnFirstTime = 'first_time';
$tableName = $installer->getTable($tableFlatOrderItem);
if ($installer->getConnection()->tableColumnExists($tableName, $columnFirstTime)) {
    $installer->getConnection()->dropColumn($tableName, $columnFirstTime);
}

//remove user_id in sales_flat_order_item
$tableFlatOrderItem = 'sales_flat_order_item';
$columnSalesRep = 'user_id';
$tableName = $installer->getTable($tableFlatOrderItem);
if ($installer->getConnection()->tableColumnExists($tableName, $columnSalesRep)) {
    $installer->getConnection()->dropColumn($tableName, $columnSalesRep);
}

//add quoteadv_product_id to quoteadv_group_price_info
$tableName = $installer->getTable('quoteadv_group_price_info');
$columnQuoteadvProductId = 'quoteadv_product_id';
if (!$installer->getConnection()->tableColumnExists($tableName, $columnQuoteadvProductId)) {
    $installer->getConnection()
        ->addColumn(
            $tableName,
            $columnQuoteadvProductId,
            array(
                'type'    => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'length'  => '10',
                'comment' => 'Quoteadv product id',
                'after'   => 'product_id'
            )
        );
} else {
    //on new installations, this field already exists and therefore an error message is not necessary
    //$message = 'Error adding column '.$column_subtotal.' to table '.$installer->getTable($table_qqadvaddress).': Column already exist';
    //Mage::log('Upgrade Error: ' .$message, null, 'c2q_exception.log', true);
}

 $installer->endSetup();
