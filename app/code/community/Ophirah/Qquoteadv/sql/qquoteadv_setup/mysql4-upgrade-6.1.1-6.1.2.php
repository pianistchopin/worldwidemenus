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

if (!$installer->getConnection()->tableColumnExists($installer->getTable('quoteadv_customer'), 'extra_field_5')) {
    $installer->run("
        ALTER TABLE `{$installer->getTable('quoteadv_customer')}` ADD `extra_field_5` VARCHAR(255) AFTER `extra_field_4`;
    ");
}

if (!$installer->getConnection()->tableColumnExists($installer->getTable('quoteadv_customer'), 'extra_field_6')) {
    $installer->run("
        ALTER TABLE `{$installer->getTable('quoteadv_customer')}` ADD `extra_field_6` VARCHAR(255) AFTER `extra_field_5`;
    ");
}

if (!$installer->getConnection()->tableColumnExists($installer->getTable('quoteadv_customer'), 'extra_field_7')) {
    $installer->run("
        ALTER TABLE `{$installer->getTable('quoteadv_customer')}` ADD `extra_field_7` VARCHAR(255) AFTER `extra_field_6`;
    ");
}

if (!$installer->getConnection()->tableColumnExists($installer->getTable('quoteadv_customer'), 'extra_field_8')) {
    $installer->run("
        ALTER TABLE `{$installer->getTable('quoteadv_customer')}` ADD `extra_field_8` VARCHAR(255) AFTER `extra_field_7`;
    ");
}

if (!$installer->getConnection()->tableColumnExists($installer->getTable('quoteadv_customer'), 'extra_field_9')) {
    $installer->run("
        ALTER TABLE `{$installer->getTable('quoteadv_customer')}` ADD `extra_field_9` VARCHAR(255) AFTER `extra_field_8`;
    ");
}

if (!$installer->getConnection()->tableColumnExists($installer->getTable('quoteadv_customer'), 'extra_field_10')) {
    $installer->run("
        ALTER TABLE `{$installer->getTable('quoteadv_customer')}` ADD `extra_field_10` VARCHAR(255) AFTER `extra_field_9`;
    ");
}

if (!$installer->getConnection()->tableColumnExists($installer->getTable('quoteadv_customer'), 'extra_field_11')) {
    $installer->run("
        ALTER TABLE `{$installer->getTable('quoteadv_customer')}` ADD `extra_field_11` VARCHAR(255) AFTER `extra_field_10`;
    ");
}

if (!$installer->getConnection()->tableColumnExists($installer->getTable('quoteadv_customer'), 'extra_field_12')) {
    $installer->run("
        ALTER TABLE `{$installer->getTable('quoteadv_customer')}` ADD `extra_field_12` VARCHAR(255) AFTER `extra_field_11`;
    ");
}

$installer->endSetup();
