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
$read = $installer->getConnection();
$dbname = (string)Mage::getConfig()->getNode('global/resources/default_setup/connection/dbname');
$installer->startSetup();
$crmMessageTable = $installer->getTable('quoteadv_crmaddon_messages');

// Add substatus
// add user_id
$checkIfColumnExistResults = "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '{$crmMessageTable}' AND COLUMN_NAME = 'user_id' and TABLE_SCHEMA = '$dbname' ";
$rows = $read->fetchAll($checkIfColumnExistResults);
if(!count($rows)) {
    $installer->run("
        ALTER TABLE `{$crmMessageTable}` ADD `user_id` int(10) default NULL;
    ");
}

// add customer_id
$checkIfColumnExistResults = "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '{$crmMessageTable}' AND COLUMN_NAME = 'customer_id' and TABLE_SCHEMA = '$dbname' ";
$rows = $read->fetchAll($checkIfColumnExistResults);
if(!count($rows)) {
    $installer->run("
        ALTER TABLE `{$crmMessageTable}` ADD `customer_id` int(10) default NULL;
    ");
}

// add send_from_frontend
$checkIfColumnExistResults = "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '{$crmMessageTable}' AND COLUMN_NAME = 'send_from_frontend' and TABLE_SCHEMA = '$dbname' ";
$rows = $read->fetchAll($checkIfColumnExistResults);
if(!count($rows)) {
    $installer->run("
        ALTER TABLE `{$crmMessageTable}` ADD `send_from_frontend` tinyint(1) default '0';
    ");
}

$installer->endSetup();
