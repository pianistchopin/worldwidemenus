<?php
$installer = $this;
/* @var $installer Mage_Catalog_Model_Resource_Eav_Mysql4_Setup */
$installer->startSetup();

$installer->run("
CREATE TABLE IF NOT EXISTS `{$this->getTable('adjicon/cpp')}` (
`id` mediumint(8) unsigned NOT NULL auto_increment,
  `vya_image_id` mediumint(8) unsigned NOT NULL,
  `cpp_option_id` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `FK_CATALOG_PRODUCT_OPTION_ADJICON_IMG` FOREIGN KEY (`vya_image_id`) REFERENCES `{$this->getTable('adjicon/image')}` (`image_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_CATALOG_PRODUCT_OPTION_AITCPP_OPTION` FOREIGN KEY (`cpp_option_id`) REFERENCES `{$this->getTable('catalog/product_option')}` (`option_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup();