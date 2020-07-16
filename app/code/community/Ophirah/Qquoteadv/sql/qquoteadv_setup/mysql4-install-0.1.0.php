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

//DROP TABLE IF EXISTS `{$installer->getTable('quoteadv_customer')}`;
if(!$installer->tableExists($installer->getTable('quoteadv_customer'))){
    $installer->run("
        CREATE TABLE `{$installer->getTable('quoteadv_customer')}` (
            `quote_id` int(10) unsigned NOT NULL auto_increment,
            `created_at` datetime NOT NULL default '0000-00-00 00:00:00',
            `updated_at` datetime NOT NULL default '0000-00-00 00:00:00',
            `is_quote` tinyint(1) NOT NULL default '2',
            `status` tinyint(1) NOT NULL default '1',
            `customer_id` bigint(20) unsigned NOT NULL default '0',
            `firstname` varchar(255) default NULL,
            `middlename` varchar(40) default NULL,
            `lastname` varchar(255) default NULL,
            `company` varchar(255) default NULL,
            `email` varchar(255) default NULL,
            `country_id` varchar(255) default NULL,
            `region` varchar(255) default NULL,
            `city` varchar(255) default NULL,
            `address` varchar(255) default NULL,
            `postcode` varchar(255) default NULL,
            `telephone` varchar(255) default NULL,
            `fax` varchar(255) default NULL,
            `client_request` text default NULL,
            PRIMARY KEY  (`quote_id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='Quotes ';
    ");
}

//DROP TABLE IF EXISTS `{$installer->getTable('quoteadv_product')}`;
if(!$installer->tableExists($installer->getTable('quoteadv_product'))){
    $installer->run("
        CREATE TABLE `{$installer->getTable('quoteadv_product')}` (
            `id` int(10) unsigned NOT NULL auto_increment,
            `quote_id` int(10) unsigned default '0',
            `product_id` int(10) unsigned NOT NULL,
            `store_id` smallint(5) unsigned NOT NULL,
            `qty` int(10) NOT NULL default '0',
            `attribute` text default NULL,
            `client_request` text default NULL,
            PRIMARY KEY  (`id`),
            KEY `FK_ quoteadv_quote_quote_id` (`quote_id`),
            KEY `FK_ quoteadv_quote_product_id` (`product_id`),
            KEY `FK_ quoteadv_quote_store_id` (`store_id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='Quote products';

        ALTER TABLE `{$installer->getTable('quoteadv_product')}`
            ADD CONSTRAINT `FK_ quoteadv_quote_quote_id` FOREIGN KEY (`quote_id`) REFERENCES `{$installer->getTable('quoteadv_customer')}` (`quote_id`) ON DELETE CASCADE ON UPDATE CASCADE,
            ADD CONSTRAINT `FK_ quoteadv_quote_product_id` FOREIGN KEY (`product_id`) REFERENCES  `{$installer->getTable('catalog_product_entity')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
            ADD CONSTRAINT `FK_ quoteadv_quote_store_id` FOREIGN KEY (`store_id`) REFERENCES  `{$installer->getTable('core_store')}` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE;
    ");
}

//DROP TABLE IF EXISTS `{$installer->getTable('quoteadv_request_item')}`;
if(!$installer->tableExists($installer->getTable('quoteadv_request_item'))) {
    $installer->run("
        CREATE TABLE `{$installer->getTable('quoteadv_request_item')}` (
            `request_id` int(10) NOT NULL auto_increment,
            `quote_id` int(10) unsigned default '0',
            `product_id` int(10) unsigned NOT NULL,
            `request_qty` smallint(5) unsigned NOT NULL default '0',
            `owner_base_price` DECIMAL(12,4) NOT NULL default '0.0000',
            PRIMARY KEY  (`request_id`),
            KEY `FK_ quoteadv_request_item_product_id` (`product_id`),
            KEY `FK_ quoteadv_request_item_quote_id` (`quote_id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='Quote request items';

        ALTER TABLE `{$installer->getTable('quoteadv_request_item')}`
            ADD CONSTRAINT `FK_ quoteadv_request_item_product_id` FOREIGN KEY (`product_id`) REFERENCES  `{$installer->getTable('catalog_product_entity')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
            ADD CONSTRAINT `FK_ quoteadv_request_item_quote_id` FOREIGN KEY (`quote_id`) REFERENCES `{$installer->getTable('quoteadv_customer')}` (`quote_id`) ON DELETE CASCADE ON UPDATE CASCADE;
        ");
}

$installer->endSetup();
