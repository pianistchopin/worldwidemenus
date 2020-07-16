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


//DROP TABLE IF EXISTS `{$installer->getTable('quoteadv_audit_trail')}`;
if(!$installer->tableExists($installer->getTable('quoteadv_audit_trail'))) {
    $installer->run("
        CREATE TABLE `{$installer->getTable('quoteadv_audit_trail')}` (
            `trail_id` int(10) unsigned NOT NULL auto_increment,
            `user_id` int(10) unsigned default NULL,
            `quote_id` int(10) unsigned NOT NULL default '0',
            `message` TEXT NOT NULL default '',
            `created_at` datetime NOT NULL default '0000-00-00 00:00:00',
            `updated_at` datetime NOT NULL default '0000-00-00 00:00:00',
            PRIMARY KEY  (`trail_id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='Quotes';

        ALTER TABLE `{$installer->getTable('quoteadv_audit_trail')}`
        ADD CONSTRAINT `FK_ quoteadv_audit_trail_user_id` FOREIGN KEY (`user_id`) REFERENCES `{$installer->getTable('admin/user')}` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;
    ");
}

$installer->endSetup();
