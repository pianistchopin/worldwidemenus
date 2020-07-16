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
$read = $installer->getConnection();
$dbname = (string)Mage::getConfig()->getNode('global/resources/default_setup/connection/dbname');
$installer->startSetup();

// Add edit_increment
$checkIfColumnExistResults = "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '{$installer->getTable('quoteadv_customer')}' AND COLUMN_NAME = 'edit_increment' and TABLE_SCHEMA = '$dbname' ";
$rows = $read->fetchAll($checkIfColumnExistResults);

if(!count($rows)) {
    $installer->run("
        ALTER TABLE `{$installer->getTable('quoteadv_customer')}` ADD `edit_increment` INT(11) DEFAULT NULL AFTER `increment_id`;
    ");
}

// Add original_increment_id
$checkIfColumnExistResults = "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '{$installer->getTable('quoteadv_customer')}' AND COLUMN_NAME = 'original_increment_id' and TABLE_SCHEMA = '$dbname' ";
$rows = $read->fetchAll($checkIfColumnExistResults);

if(!count($rows)) {
    $installer->run("
        ALTER TABLE `{$installer->getTable('quoteadv_customer')}` ADD `original_increment_id` VARCHAR(50) DEFAULT NULL AFTER `increment_id`;
    ");
}

// Add relation_child_id
$checkIfColumnExistResults = "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '{$installer->getTable('quoteadv_customer')}' AND COLUMN_NAME = 'relation_child_id' and TABLE_SCHEMA = '$dbname' ";
$rows = $read->fetchAll($checkIfColumnExistResults);

if(!count($rows)) {
    $installer->run("
        ALTER TABLE `{$installer->getTable('quoteadv_customer')}` ADD `relation_child_id` VARCHAR(32) DEFAULT NULL AFTER `original_increment_id`;
    ");
}

// Add relation_child_real_id
$checkIfColumnExistResults = "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '{$installer->getTable('quoteadv_customer')}' AND COLUMN_NAME = 'relation_child_real_id' and TABLE_SCHEMA = '$dbname' ";
$rows = $read->fetchAll($checkIfColumnExistResults);

if(!count($rows)) {
    $installer->run("
        ALTER TABLE `{$installer->getTable('quoteadv_customer')}` ADD `relation_child_real_id` VARCHAR(32) DEFAULT NULL AFTER `relation_child_id`;
    ");
}

// Add relation_parent_id
$checkIfColumnExistResults = "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '{$installer->getTable('quoteadv_customer')}' AND COLUMN_NAME = 'relation_parent_id' and TABLE_SCHEMA = '$dbname' ";
$rows = $read->fetchAll($checkIfColumnExistResults);

if(!count($rows)) {
    $installer->run("
        ALTER TABLE `{$installer->getTable('quoteadv_customer')}` ADD `relation_parent_id` VARCHAR(32) DEFAULT NULL AFTER `relation_child_real_id`;
    ");
}

// Add relation_parent_real_id
$checkIfColumnExistResults = "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '{$installer->getTable('quoteadv_customer')}' AND COLUMN_NAME = 'relation_parent_real_id' and TABLE_SCHEMA = '$dbname' ";
$rows = $read->fetchAll($checkIfColumnExistResults);

if(!count($rows)) {
    $installer->run("
        ALTER TABLE `{$installer->getTable('quoteadv_customer')}` ADD `relation_parent_real_id` VARCHAR(32) DEFAULT NULL AFTER `relation_parent_id`;
    ");
}

$installer->endSetup();
