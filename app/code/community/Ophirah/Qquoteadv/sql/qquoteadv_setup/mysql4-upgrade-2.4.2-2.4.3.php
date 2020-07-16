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

//DROP TABLE IF EXISTS `{$installer->getTable('qquoteadv/product_attribute_group_allow')}`;
if(!$installer->tableExists($installer->getTable('qquoteadv/product_attribute_group_allow'))){
    $table = $installer->getConnection()
        ->newTable($installer->getTable('qquoteadv/product_attribute_group_allow'))
        ->addColumn('value_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'identity' => true,
            'nullable' => false,
            'primary' => true,
        ), 'Value ID')
        ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned' => true,
            'nullable' => false,
            'default' => '0',
        ), 'Entity ID')
        ->addColumn('all_groups', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
            'unsigned' => true,
            'nullable' => false,
            'default' => '1',
        ), 'Is Applicable To All Customer Groups')
        ->addColumn('customer_group_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
            'unsigned' => true,
            'nullable' => false,
            'default' => '0',
        ), 'Customer Group ID')
        ->addColumn('value', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
            'unsigned' => true,
            'nullable' => false,
            'default' => '0',
        ), 'Value')
        ->addColumn('website_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
            'unsigned' => true,
            'nullable' => false,
            'default' => '0',
        ), 'Website ID')
        ->addIndex($installer->getIdxName('qquoteadv/product_attribute_group_allow', array('entity_id')),
            array('entity_id'))
        ->addIndex($installer->getIdxName('qquoteadv/product_attribute_group_allow', array('customer_group_id')),
            array('customer_group_id'))
        ->addIndex($installer->getIdxName('qquoteadv/product_attribute_group_allow', array('website_id')),
            array('website_id'))
        ->setComment('Catalog Product Allow Quote Request Backend Table');

    $installer->getConnection()->createTable($table);
}

/**
 * Adding Attributes
 */
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

// Check for existing attribute
$attr = Mage::getResourceModel('catalog/eav_attribute')->loadByCode('catalog_product','group_allow_quotemode');
if (!$attr->getId()) {

    $setup->addAttribute('catalog_product', 'group_allow_quotemode', array(
        'group' => 'General',
        'input' => 'text',
        'type' => 'int',
        'label' => 'Enable Quotations',
        'source' => 'qquoteadv/catalog_product_attribute_backend_qquoteadv_group_allow',
        'backend' => 'eav/entity_attribute_backend_array',
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
        'visible' => true,
        'required' => false,
        'default_value' => '0'
    ));

} else { // Updating Attribute

    $setup->updateAttribute('catalog_product', 'group_allow_quotemode', array(
        'frontend_input' => 'text',
        'backend_type' => 'int',
        'frontend_label' => 'Enable Quotations',
        'source_model' => 'qquoteadv/catalog_product_attribute_backend_qquoteadv_group_allow',
        'backend_model' => 'eav/entity_attribute_backend_array',
        'is_required' => false,
        'default_value' => '0'
    ));

}

$installer->endSetup();
