<?php

$installer = $this;
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$installer->startSetup();

/**
 * Adding Attributes
 */
$setup->addAttribute(
    'catalog_product', 'hideprice_for_ordermode', array(
        'group' => 'Prices',
        'input' => 'select',
        'frontend_input' => 'boolean',
        'type' => 'int',
        'label' => Mage::helper('not2order')->__('Hide price'),
        'source' => 'not2order/source_alloworder',
        'backend' => 'eav/entity_attribute_backend_array',
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
        'visible' => true,
        'used_for_price_rules' => false,
        'required' => false,
        'default_value' => 0
    )
);

$installer->endSetup();
