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

/** @var Ophirah_Qquoteadv_Model_Mysql4_Setup $this */
$installer = $this;
$installer->startSetup();

if (!$installer->tableExists($installer->getTable('quoteadv_group_price_info'))) {
    $installer->run("
        CREATE TABLE `{$installer->getTable('quoteadv_group_price_info')}` (
            `group_price_id` int(10) unsigned NOT NULL auto_increment,
            `product_id` int(10) unsigned NOT NULL,
            `quote_id` int(10) unsigned NOT NULL,
            `group_price` int(10) unsigned,
            `user_id` int(10) unsigned NOT NULL,
            `customer_group_id` int(10) unsigned NOT NULL,
            `qty` int(10) unsigned NOT NULL,
            `created_at` datetime NOT NULL default '0000-00-00 00:00:00',
            `updated_at` datetime NOT NULL default '0000-00-00 00:00:00',
            PRIMARY KEY  (`group_price_id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='Quotes';
    ");
}

$table_flat_order_item = 'sales_flat_order_item';
$columnFirstTime = 'first_time';
$columnFirstTimeComment = 'Product ordered first time';
$columnSalesRep = 'user_id';
$columnSalesRepComment = 'sales representative';

/**
 * Check if column exists and if it doesn't add a new column.
 */
$tableName = $installer->getTable($table_flat_order_item);
if (!$installer->getConnection()->tableColumnExists($tableName, $columnFirstTime)) {
    $installer->getConnection()
        ->addColumn(
            $tableName,
            $columnFirstTime,
            array(
                'type'    => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
                'length'  => '1',
                'comment' => $columnFirstTimeComment
            )
        );
} else {
    //on new installations, this field already exists and therefore an error message is not necessary
    //$message = 'Error adding column '.$column_subtotal.' to table '.$installer->getTable($table_qqadvaddress).': Column already exist';
    //Mage::log('Upgrade Error: ' .$message, null, 'c2q_exception.log', true);
}

$tableName = $installer->getTable($table_flat_order_item);
if (!$installer->getConnection()->tableColumnExists($tableName, $columnSalesRep)) {
    $installer->getConnection()
        ->addColumn(
            $tableName,
            $columnSalesRep,
            array(
                'type'    => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'length'  => '10',
                'comment' => $columnSalesRepComment
            )
        );
} else {
    //on new installations, this field already exists and therefore an error message is not necessary
    //$message = 'Error adding column '.$column_subtotal.' to table '.$installer->getTable($table_qqadvaddress).': Column already exist';
    //Mage::log('Upgrade Error: ' .$message, null, 'c2q_exception.log', true);
}

$installer->endSetup();
