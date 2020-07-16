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
$table_product = 'qquoteadv/qqadvproduct';
$column_qty = 'qty';
$table_request_item = 'qquoteadv/requestitem';
$column_request_qty = 'request_qty';

/** @var Ophirah_Qquoteadv_Model_Mysql4_Setup $this */
$installer = $this;
$installer->startSetup();

$tableName = $installer->getTable($table_product);
if ($installer->getConnection()->tableColumnExists($tableName, $column_qty)) {
    $installer->getConnection()
        ->changeColumn(
            $tableName, $column_qty, $column_qty, array(
            'type' => 'decimal',
            'precision' => 12,
            'scale' => 4
        ));
}else {
    $message = 'Error modifying column '.$column_qty.' to table '.$installer->getTable($table_product).': Column does not exist';
    Mage::log('Exception: ' .$message, null, 'c2q_exception.log', true);
}

$tableName = $installer->getTable($table_request_item);
if ($installer->getConnection()->tableColumnExists($tableName, $column_request_qty)) {
    $installer->getConnection()
        ->changeColumn(
            $tableName, $column_request_qty, $column_request_qty, array(
            'type' => 'decimal',
            'precision' => 12,
            'scale' => 4
        ));
}else {
    $message = 'Error modifying column '.$column_request_qty.' to table '.$installer->getTable($table_request_item).': Column does not exist';
    Mage::log('Exception: ' .$message, null, 'c2q_exception.log', true);
}

$installer->endSetup();
