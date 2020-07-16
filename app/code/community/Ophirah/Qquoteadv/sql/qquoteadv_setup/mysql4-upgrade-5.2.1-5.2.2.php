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
$table_address 	= 'qquoteadv/address';
$column_customer_group_id	= 'customer_group_id';
$comment_customer_group_id   = 'Customer Group Id';

/** @var Ophirah_Qquoteadv_Model_Mysql4_Setup $this */
$installer = $this;
$installer->startSetup();

/**
 * Check if column exists and if it doesn't add a new column.
 */
$tableName = $installer->getTable($table_address);
if (!$installer->getConnection()->tableColumnExists($tableName, $column_customer_group_id)) {
    $installer->getConnection()
        ->addColumn(
            $tableName,
            $column_customer_group_id,
            array(
                'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'length' => 10,
                'comment' => $comment_customer_group_id
            )
        );
}
//}else {
//    $message = 'Error adding column '.$column_customer_group_id.' to table '.$installer->getTable($table_address).': Column already exist';
//    Mage::log('Upgrade Error: ' .$message, null, 'c2q_exception.log', true);
//}

//Fix shipping_type for 0.1.11 from char(1) to char(10), making it compatible with the rate_id in quoteadv_shipping_rate
$installer->run("ALTER TABLE `{$installer->getTable('quoteadv_customer')}` CHANGE `shipping_type` `shipping_type` CHAR(10) NOT NULL DEFAULT '';");

//Fix customer_group_id if not installed in 5.0.1-5.0.2
if (!$installer->getConnection()->tableColumnExists($installer->getTable('quoteadv_customer'), 'customer_group_id')) {
    $installer->run("
        ALTER TABLE `{$installer->getTable('quoteadv_customer')}` ADD `customer_group_id` VARCHAR(255);
    ");
}

//add customer_tax_class_id
if (!$installer->getConnection()->tableColumnExists($installer->getTable('quoteadv_customer'), 'customer_tax_class_id')) {
    $installer->run("
        ALTER TABLE `{$installer->getTable('quoteadv_customer')}` ADD `customer_tax_class_id` INT(10) NULL DEFAULT '0' AFTER `customer_group_id`;
    ");
}

$installer->endSetup();
