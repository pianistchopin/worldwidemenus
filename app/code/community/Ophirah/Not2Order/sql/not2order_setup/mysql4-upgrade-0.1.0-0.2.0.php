<?php

$installer = $this;
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$entityTypeId = $this->getEntityTypeId('catalog_product');
$id = $this->getAttribute($entityTypeId, 'hide_price', 'attribute_id');

$data = array('note' => Mage::helper('not2order')->__("Warning: if the price is hidden and the product is orderable, prices will show in checkout"));

$setup->updateAttribute($entityTypeId, $id, $data);

$installer->endSetup();