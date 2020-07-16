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

// Extra Options table
// To be used to create a custom option field
// DROP TABLE IF EXISTS `{$installer->getTable('quoteadv_extraoptions')}`;
if(!$installer->tableExists($installer->getTable('quoteadv_extraoptions'))) {
    $installer->run("
        CREATE TABLE `{$installer->getTable('quoteadv_extraoptions')}` (
            `option_id` int(10) unsigned NOT NULL auto_increment,
            `option_type` int(10) DEFAULT NULL,
            `value` TEXT DEFAULT NULL,
            `label` TEXT DEFAULT NULL,
            `order` int(10) DEFAULT NULL,
            `title` int(10) DEFAULT NULL,
            `status` tinyint(1) NOT NULL default '1',
            PRIMARY KEY  (`option_id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='Quotes';
    ");
}

$quoteAddressTable = 'quoteadv_customer';
$tableName = $installer->getTable($quoteAddressTable);
$columns = array(
    'proposal_sent' => array(
        'type' => Varien_Db_Ddl_Table::TYPE_DATETIME,
        'default' => '0000-00-00 00:00:00',
        'after' =>'created_at',
        'NULLABLE' => false,
        'comment' => 'Proposal Sent'
    ),
    'no_reminder' => array(
        'type' => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
        'length' => 1,
        'default' => 0,
        'after' =>'no_expiry',
        'comment' => 'No Reminder'
    ),
    'reminder' => array(
        'type' => Varien_Db_Ddl_Table::TYPE_DATE,
        'after' =>'expiry',
        'comment' => 'Reminder'
    ),
    'create_hash' => array(
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length' => 40,
        'default' => null,
        'after' =>'hash',
        'comment' => 'Create Hash'
    ),
    'salesrule' => array(
        'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'default' => null,
        'after' => 'status',
        'comment' => 'Salesrule'
    ),
);
foreach($columns as $columnName => $columnDefinition){
    if (!$installer->getConnection()->addColumn($tableName, $columnName, $columnDefinition)) {
        $message = 'Error modifying column '.$columnName.' to table '.$tableName.': Column does already exist';
        Mage::log('Exception: ' .$message, null, 'c2q_exception.log', true);
    }
}

$installer->endSetup();
