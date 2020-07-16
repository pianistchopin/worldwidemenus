<?php
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();
$level = Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE;
$attrOrder = Mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', 'allowed_to_ordermode');
$attrOrder->setIsGlobal($level)->save();

$attrHide = Mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', 'hideprice_for_ordermode');
$attrHide->setIsGlobal($level)->save();

$installer->endSetup();