<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup  */
$installer->startSetup();

$installer->run("
-- DROP TABLE IF EXISTS `{$this->getTable('aitcg/sharedimage')}`;
CREATE TABLE IF NOT EXISTS `{$this->getTable('aitcg/sharedimage')}` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shared_img_id` varchar(50) NOT NULL DEFAULT '0',
  `product_id` int(10) unsigned NOT NULL DEFAULT 0,
  KEY (`id`),
  PRIMARY KEY (`shared_img_id`),
  KEY (`product_id`),
  CONSTRAINT `FK_AITCG_SHAREDIMAGE_CATALOG_PRODUCT_ID` FOREIGN KEY (`product_id`) REFERENCES `{$installer->getTable('catalog/product')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

");

$installer->endSetup(); 