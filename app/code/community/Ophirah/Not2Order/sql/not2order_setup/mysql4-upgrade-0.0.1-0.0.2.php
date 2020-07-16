<?php

$installer = $this;
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$installer->startSetup();

/**
 * Adding Attributes
 */
$attr = Mage::getResourceModel('catalog/eav_attribute')
    ->loadByCode('catalog_product', 'allowed_to_ordermode');
if (!$attr->getId()) {
    $setup->addAttribute(
        'catalog_product', 'allowed_to_ordermode', array(
            'group' => 'General',
            'input' => 'select',
            'frontend_input' => 'boolean',
            'type' => 'int',
            'label' => Mage::helper('not2order')->__('Allow to Order mode'),
            'source' => 'not2order/source_alloworder',
            'backend' => 'eav/entity_attribute_backend_array',
            'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
            'visible' => true,
            'used_for_price_rules' => false,
            'required' => false,
            'default_value' => '1'
        )
    );
}

$installer->endSetup();