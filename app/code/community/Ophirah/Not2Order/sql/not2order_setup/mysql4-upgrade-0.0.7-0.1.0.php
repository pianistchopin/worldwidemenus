<?php

$installer = $this;
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$entityTypeId = $this->getEntityTypeId('catalog_product');
$id = $this->getAttribute($entityTypeId, 'hideprice_for_ordermode', 'attribute_id');

$checkDuplicate = $this->getAttribute($entityTypeId, 'hide_price', 'attribute_id');
if (!$checkDuplicate) {
    $data = array('frontend_label' => Mage::helper('not2order')->__('Hide price'),
        'attribute_code' => 'hide_price',
        'source_model' => 'not2order/source_allowprice');

    $setup->updateAttribute($entityTypeId, $id, $data);
}

$installer->endSetup();