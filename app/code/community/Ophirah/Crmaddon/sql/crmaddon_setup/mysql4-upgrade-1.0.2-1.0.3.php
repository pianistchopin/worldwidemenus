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
$crmMessageTable = $installer->getTable('quoteadv_crmaddon_messages');

// remove foreign key `FK_ quoteadv_crmaddon_messages_template_id`
$indexList = $installer->getConnection()->getIndexList($crmMessageTable);
if(array_key_exists('FK_ quoteadv_crmaddon_messages_template_id', $indexList)) {
    //check for foreign key on the index
    $foreignKeyList = $installer->getConnection()->getForeignKeys($crmMessageTable);
    if(array_key_exists('FK_ quoteadv_crmaddon_messages_template_id', $foreignKeyList)) {
        //drop foreign key
        $installer->run("ALTER TABLE `{$crmMessageTable}` DROP FOREIGN KEY `FK_ quoteadv_crmaddon_messages_template_id`;");
    }

    //drop key/index
    $installer->run("ALTER TABLE `{$crmMessageTable}` DROP INDEX `FK_ quoteadv_crmaddon_messages_template_id`;");
}

// remove foreign key `FK_quoteadv_crmaddon_messages_template_id`
$indexList = $installer->getConnection()->getIndexList($crmMessageTable);
if(array_key_exists('FK_quoteadv_crmaddon_messages_template_id', $indexList)) {
    //check for foreign key on the index
    $foreignKeyList = $installer->getConnection()->getForeignKeys($crmMessageTable);
    if(array_key_exists('FK_quoteadv_crmaddon_messages_template_id', $foreignKeyList)) {
        //drop foreign key
        $installer->run("ALTER TABLE `{$crmMessageTable}` DROP FOREIGN KEY `FK_quoteadv_crmaddon_messages_template_id`;");
    }

    //drop key/index
    $installer->run("ALTER TABLE `{$crmMessageTable}` DROP INDEX `FK_quoteadv_crmaddon_messages_template_id`;");
}

$installer->endSetup();
