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

//DROP TABLE IF EXISTS `{$installer->getTable('quoteadv_crmaddon_messages')}`;
if(!$installer->tableExists($installer->getTable('quoteadv_crmaddon_messages'))) {
    //disable key checks
    $installer->run("SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0");

    //create table
    $installer->run("
        CREATE TABLE `{$installer->getTable('quoteadv_crmaddon_messages')}` (
                `message_id` int(10) unsigned NOT NULL auto_increment,
                `created_at` datetime NOT NULL default '0000-00-00 00:00:00',
                `updated_at` datetime NOT NULL default '0000-00-00 00:00:00',
                `quote_id` int(10) unsigned default '0',
                `status` tinyint(1) NOT NULL default '1',
                `email_address` text default NULL,
                `subject` varchar(255) default NULL,
                `template_id` int(10) unsigned default '0',
                `message` text default NULL,
                PRIMARY KEY  (`message_id`),
                KEY `FK_quoteadv_crmaddon_messages_quote_id` (`quote_id`),
                KEY `FK_quoteadv_crmaddon_messages_template_id` (`template_id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='CRMaddon messages';
    ");

    //add constraint
    $installer->run("
        ALTER TABLE `{$installer->getTable('quoteadv_crmaddon_messages')}`
            ADD CONSTRAINT `FK_quoteadv_crmaddon_messages_quote_id` FOREIGN KEY (`quote_id`) REFERENCES `{$installer->getTable('quoteadv_customer')}` (`quote_id`) ON DELETE CASCADE ON UPDATE CASCADE
        ;
    ");

    //re-enable key check
    $installer->run("SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS=0, 0, 1)");
}

//DROP TABLE IF EXISTS `{$installer->getTable('quoteadv_crmaddon_templates')}`;
if(!$installer->tableExists($installer->getTable('quoteadv_crmaddon_templates'))) {
    $installer->run("
        CREATE TABLE `{$installer->getTable('quoteadv_crmaddon_templates')}` (
                `template_id` int(10) unsigned NOT NULL auto_increment,
                `name` varchar(255) default NULL,
                `subject` text default NULL,
                `template` text default NULL,
                `default` tinyint(1) NOT NULL default '0',
                `status` tinyint(1) NOT NULL default '1',
                PRIMARY KEY  (`template_id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='CRMaddon templates';
    ");


    $installer->run("
        ALTER TABLE `{$installer->getTable('quoteadv_crmaddon_messages')}`
            ADD CONSTRAINT `FK_quoteadv_crmaddon_messages_template_id` FOREIGN KEY (`template_id`) REFERENCES `{$installer->getTable('quoteadv_crmaddon_templates')}` (`template_id`) ON DELETE CASCADE ON UPDATE CASCADE
        ;
    ");
}

$installer->endSetup();
