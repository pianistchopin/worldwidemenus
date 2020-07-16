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
 * @package     Crmaddon
 * @copyright   Copyright (c) 2020 Cart2Quote B.V. (https://www.cart2quote.com)
 * @license     https://www.cart2quote.com/ordering-licenses(https://www.cart2quote.com)
 */

/** @var Ophirah_Crmaddon_Model_Mysql4_Setup $this */
$installer = $this;
$installer->startSetup();

$quoteAddressTable = 'quoteadv_crmaddon_messages';
$tableName = $installer->getTable($quoteAddressTable);
$columns = array(
    'customer_notified' => array(
        'type' => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
        'length' => 1,
        'default' => null,
        'after' => 'status',
        'comment' => 'Customer Notified'
    )
);

foreach($columns as $columnName => $columnDefinition){
    if (!$installer->getConnection()->addColumn($tableName, $columnName, $columnDefinition)) {
        $message = 'Error modifying column '.$columnName.' to table '.$tableName.': Column does already exist';
        Mage::log('Exception: ' .$message, null, 'c2q_exception.log', true);
    }
}

// If messages exist, update status
// to 'customer notified'
$sql = "SELECT `message_id` , `customer_notified` FROM `{$installer->getTable('quoteadv_crmaddon_messages')}` WHERE `customer_notified` IS NULL";
$result = $installer->getConnection()->query($sql);

if (isset($result)){
    foreach ($result as $item) {
        $update = "UPDATE {$installer->getTable('quoteadv_crmaddon_messages')} SET `customer_notified`='1' WHERE (`message_id`='{$item['message_id']}')";
        $installer->run($update);
    }
}
$installer->endSetup();
