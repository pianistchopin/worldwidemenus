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
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$installer->startSetup();

$entityTypeId = (int)$setup->getEntityTypeId('catalog_product');
$id = (int)$setup->getAttributeId('catalog_product', 'quotemode_conditions');

if ($id == 0) { // Adding Attribute

    $setup->addAttribute('catalog_product', 'quotemode_conditions', array(
        'group' => 'General',
        'input' => 'select',
        'type' => 'int',
        'label' => 'Quotation Conditions',
        'source' => 'qquoteadv/source_conditions',
        'backend' => 'eav/entity_attribute_backend_array',
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
        'visible' => true,
        'required' => false,
        'default_value' => '0',
        'note' => 'Product needs to be allowed for quotations before setting conditions'
    ));

} else { // Updating Attribute

    $setup->updateAttribute('catalog_product', 'quotemode_conditions', array(
        'frontend_input' => 'select',
        'backend_type' => 'int',
        'frontend_label' => 'Quotation Conditions',
        'source_model' => 'qquoteadv/source_conditions',
        'backend_model' => 'eav/entity_attribute_backend_array',
        'is_required' => false,
        'default_value' => '0',
        'note' => 'Product needs to be allowed for quotations before setting conditions'
    ));

}

//DROP TABLE IF EXISTS  `{$installer->getTable('quoteadv_quote_address')}`;
// Add address table
if(!$installer->tableExists($installer->getTable('quoteadv_quote_address'))) {
    $installer->run("
    CREATE TABLE `{$installer->getTable('quoteadv_quote_address')}` (
        `address_id` int(10) unsigned NOT NULL auto_increment,
        `quote_id` int(10) unsigned NOT NULL default '0',
        `created_at` datetime NOT NULL default '0000-00-00 00:00:00',
        `updated_at` datetime NOT NULL default '0000-00-00 00:00:00',

        `customer_id` int(10) unsigned default NULL,
        `save_in_address_book` tinyint(1) default '0',
        `customer_address_id` int(10) unsigned default NULL,
        `address_type` varchar(255) default NULL,
        `email` varchar(255) default NULL,
        `prefix` varchar(40) default NULL,
        `firstname` varchar(255) default NULL,
        `middlename` varchar(40) default NULL,
        `lastname` varchar(255) default NULL,
        `suffix` varchar(40) default NULL,
        `company` varchar(255) default NULL,
        `street` varchar(255) default NULL,
        `city` varchar(255) default NULL,
        `region` varchar(255) default NULL,
        `region_id` int(10) unsigned default NULL,
        `postcode` varchar(255) default NULL,
        `country_id` varchar(255) default NULL,
        `telephone` varchar(255) default NULL,
        `fax` varchar(255) default NULL,

        `same_as_billing` tinyint(1) unsigned  default '0',
        `free_shipping` tinyint(1) unsigned  default '0',
        `collect_shipping_rates` tinyint(1) unsigned  default '0',
        `shipping_method` varchar(255) default '',
        `shipping_description` varchar(255)  default '',
        `weight` decimal(12,4) default '0.0000',

        `subtotal` decimal(12,4) default '0.0000',
        `base_subtotal` decimal(12,4) default '0.0000',
        `subtotal_with_discount` decimal(12,4) default '0.0000',
        `base_subtotal_with_discount` decimal(12,4) default '0.0000',
        `tax_amount` decimal(12,4)default '0.0000',
        `base_tax_amount` decimal(12,4) default '0.0000',
        `shipping_amount` decimal(12,4) default '0.0000',
        `base_shipping_amount` decimal(12,4) default '0.0000',
        `shipping_tax_amount` decimal(12,4) default NULL,
        `base_shipping_tax_amount` decimal(12,4) default NULL,
        `discount_amount` decimal(12,4) default '0.0000',
        `base_discount_amount` decimal(12,4)default '0.0000',
        `grand_total` decimal(12,4) default '0.0000',
        `base_grand_total` decimal(12,4) default '0.0000',

        `customer_notes` text,
        PRIMARY KEY  (`address_id`),
        KEY `FK_QUOTEADV_QUOTE_ADDRESS_QUOTE_ID` (`quote_id`),
        CONSTRAINT `FK_QUOTEADV_QUOTE_ADDRESS_QUOTE_ID` FOREIGN KEY (`quote_id`) REFERENCES `{$installer->getTable('quoteadv_customer')}` (`quote_id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");
}

// DROP TABLE IF EXISTS  `{$installer->getTable('quoteadv_shipping_rate')}`;
// Add shipping rate table
if(!$installer->tableExists($installer->getTable('quoteadv_shipping_rate'))) {
    $installer->run("
        CREATE TABLE `{$installer->getTable('quoteadv_shipping_rate')}` (
            `rate_id` int(10) unsigned NOT NULL auto_increment,
            `address_id` int(10) unsigned NOT NULL default '0',
            `created_at` datetime NOT NULL default '0000-00-00 00:00:00',
            `updated_at` datetime NOT NULL default '0000-00-00 00:00:00',
            `carrier` varchar(255) default NULL,
            `carrier_title` varchar(255) default NULL,
            `code` varchar(255) default NULL,
            `method` varchar(255) default NULL,
            `method_description` text,
            `price` decimal(12,4) NOT NULL default '0.0000',
            `error_message` text,
            `method_title` text,
            PRIMARY KEY  (`rate_id`),
            KEY `FK_QUOTEADV_SHIPPING_RATE_ADDRESS` (`address_id`),
            CONSTRAINT `FK_QUOTEADV_SHIPPING_RATE_ADDRESS` FOREIGN KEY (`address_id`) REFERENCES `{$installer->getTable('quoteadv_quote_address')}` (`address_id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ");
}

$installer->endSetup();
