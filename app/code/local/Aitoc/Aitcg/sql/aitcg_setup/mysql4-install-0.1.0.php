<?php

$installer = $this;

$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('catalog/product_option_aitimage')};
CREATE TABLE `{$this->getTable('catalog/product_option_aitimage')}` (
 `option_aitimage_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
 `option_id` int(10) unsigned NOT NULL DEFAULT '0',
 `store_id` smallint(5) unsigned NOT NULL DEFAULT '0',
 `image_template_id` int(10) unsigned NOT NULL DEFAULT '0',
 `area_size_x` smallint(4) unsigned NOT NULL DEFAULT '0',
 `area_size_y` smallint(4) unsigned NOT NULL DEFAULT '0',
 `area_offset_x` smallint(4) unsigned NOT NULL DEFAULT '0',
 `area_offset_y` smallint(4) unsigned NOT NULL DEFAULT '0',
 PRIMARY KEY (`option_aitimage_id`),
 UNIQUE KEY `UNQ_OPTION_STORE` (`option_id`,`store_id`),
 KEY `CATALOG_PRODUCT_OPTION_AITIMAGE_OPTION` (`option_id`),
 KEY `CATALOG_PRODUCT_OPTION_AITIMAGE_TITLE_STORE` (`store_id`),
 CONSTRAINT `FK_CATALOG_PRODUCT_OPTION_AITIMAGE_OPTION` FOREIGN KEY (`option_id`) REFERENCES `{$this->getTable('catalog/product_option')}` (`option_id`) ON DELETE CASCADE ON UPDATE CASCADE,
 CONSTRAINT `FK_CATALOG_PRODUCT_OPTION_AITIMAGE_PRICE_STORE` FOREIGN KEY (`store_id`) REFERENCES `{$this->getTable('core/store')}` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

 
-- DROP TABLE IF EXISTS {$this->getTable('aitcg/image')};
CREATE TABLE `{$this->getTable('aitcg/image')}` (
 `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
 `temp_id` int(11) unsigned NOT NULL DEFAULT '0', 
 `file_name` varchar(128) NOT NULL,
 `preview_width` smallint(4) NOT NULL DEFAULT '0',
 `preview_height` smallint(4) NOT NULL DEFAULT '0',
 `is_order` tinyint(1) NOT NULL DEFAULT '0',
 `create_time` datetime NOT NULL,
 `x` mediumint(9) NOT NULL DEFAULT '0',
 `y` mediumint(9) NOT NULL DEFAULT '0',
 `scale_x` float(7,4) NOT NULL DEFAULT '1.0000',
 `scale_y` float(7,4) NOT NULL DEFAULT '1.0000',
 `angle` smallint(6) NOT NULL DEFAULT '0',
 `image_template_id` int(10) unsigned NOT NULL DEFAULT '0',
 `area_size_x` smallint(4) unsigned NOT NULL DEFAULT '0',
 `area_size_y` smallint(4) unsigned NOT NULL DEFAULT '0',
 `area_offset_x` smallint(4) unsigned NOT NULL DEFAULT '0',
 `area_offset_y` smallint(4) unsigned NOT NULL DEFAULT '0', 
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

ALTER TABLE {$this->getTable('catalog_product_entity_media_gallery_value')}
  ADD COLUMN `cgimage` tinyint(1) NOT NULL  DEFAULT '0' after `disabled`; 

-- DROP TABLE IF EXISTS {$this->getTable('aitcg/image_store')};
CREATE TABLE `{$this->getTable('aitcg/image_store')}` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `image_id` int(10) unsigned NOT NULL DEFAULT '0',
  `order_id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `AITCG_IMAGE_STORE_IMAGE` (`image_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

ALTER TABLE `{$this->getTable('aitcg/image_store')}`
  ADD CONSTRAINT `FK_AITCG_IMAGE_STORE_IMAGE` FOREIGN KEY (`image_id`) REFERENCES `{$this->getTable('aitcg/image')}` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

");

$installer->endSetup(); 
