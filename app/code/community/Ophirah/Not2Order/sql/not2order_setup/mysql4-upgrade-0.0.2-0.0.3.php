<?php
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$entityTypeId = $this->getEntityTypeId('catalog_product');
$id = $this->getAttribute($entityTypeId, 'allowed_to_ordermode', 'attribute_id');
$data = array('default_value' => '1');
$this->updateAttribute($entityTypeId, $id, $data);

$installer->endSetup();