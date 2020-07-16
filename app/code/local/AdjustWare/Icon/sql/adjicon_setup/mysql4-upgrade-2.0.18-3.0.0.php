<?php

$installer = $this;
/* @var $installer Mage_Catalog_Model_Resource_Eav_Mysql4_Setup */
$installer->startSetup();

$installer->run("
TRUNCATE TABLE `{$this->getTable('adjicon/icon')}`;

ALTER TABLE `{$this->getTable('adjicon/attribute')}`
MODIFY `columns_num` TINYINT(1) NOT NULL DEFAULT '2',
MODIFY `show_images` TINYINT(1) NOT NULL DEFAULT '1',
ADD `show_qty` TINYINT( 1 ) NOT NULL DEFAULT '1' AFTER `show_images`,
ADD `show_images_product` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `show_qty`,
ADD `show_images_configurable` TINYINT( 1 ) NOT NULL DEFAULT '1' AFTER `show_images_product`,
ADD `config_option_type` TINYINT( 1 ) NOT NULL DEFAULT '2' AFTER `show_images_configurable`,
ADD `visualization_type` TINYINT( 1 ) NOT NULL AFTER `attribute_code`,
ADD `icon_type` TINYINT( 1 ) NOT NULL AFTER `visualization_type`,
ADD `color_icon_type` TINYINT( 1 ) NOT NULL AFTER `icon_type`,
ADD `icon_color` varchar(255) NOT NULL DEFAULT '#FFFFFF' AFTER `color_icon_type`,
ADD `icon_text_color` varchar(255) NOT NULL DEFAULT '#000000' AFTER `icon_color`;

CREATE TABLE IF NOT EXISTS `{$this->getTable('adjicon/color')}` (
  `color_id` mediumint(8) unsigned NOT NULL auto_increment,
  `option_id` mediumint(8) unsigned NOT NULL,
  `color` varchar(255) NOT NULL,
  PRIMARY KEY (`color_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `{$this->getTable('adjicon/image')}` (
  `image_id` mediumint(8) unsigned NOT NULL auto_increment,
  `product_id` mediumint(8) unsigned NOT NULL,
  `option_id` mediumint(8) unsigned NOT NULL,
  `file` varchar(255) NOT NULL,
  `label` varchar(255) NOT NULL,
  `base_image` TINYINT(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`image_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");
$installer->endSetup();