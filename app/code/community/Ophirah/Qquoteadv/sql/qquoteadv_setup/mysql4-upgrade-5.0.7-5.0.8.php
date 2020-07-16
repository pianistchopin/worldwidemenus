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

/**
 * Adding Attributes
 */
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

// Check for existing id
$entityTypeId = (int)$setup->getEntityTypeId('catalog_product');
$id = (int)$setup->getAttributeId('catalog_product', 'cost_tier_price');

if ($id == 0) { // Adding Attribute

    $setup->addAttribute('catalog_product', 'cost_tier_price', array(
        'group' => 'Prices',
        'input' => 'text',
        'type' => 'int',
        'label' => 'Tier Cost Price',
        'source' => null,
        'backend' => 'qquoteadv/catalog_product_attribute_backend_qquoteadv_tiercost',
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
        'visible' => true,
        'required' => false,
        'default_value' => '0'
    ));

} else { // Updating Attribute

    $setup->updateAttribute('catalog_product', 'cost_tier_price', array(
        'frontend_input' => 'text',
        'backend_type' => 'int',
        'frontend_label' => 'Tier Cost Price',
        'backend_model' => 'qquoteadv/catalog_product_attribute_backend_qquoteadv_tiercost',
        'source_model' => null,
        'is_required' => false,
        'default_value' => '0'
    ));

}

//DROP TABLE IF EXISTS {$installer->getTable('quoteadv_tier_cost')};
if (!$installer->tableExists($installer->getTable('quoteadv_tier_cost'))) {
    $installer->run("
        CREATE TABLE {$installer->getTable('quoteadv_tier_cost')} (
            `value_id` int(11) NOT NULL auto_increment,
            `entity_id` int(10) unsigned NOT NULL default '0',
            `all_groups` tinyint (1)unsigned NOT NULL DEFAULT '1',
            `customer_group_id` smallint(5) unsigned NOT NULL default '0',
            `qty` decimal(12,4) NOT NULL default 1,
            `value` decimal(12,4) NOT NULL default '0.0000',
            `website_id` smallint(5) unsigned NOT NULL,
            PRIMARY KEY  (`value_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ");
}

$installer->endSetup();
