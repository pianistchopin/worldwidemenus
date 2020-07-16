<?php

@set_time_limit(0);
$installer = $this;
$installer->startSetup();

// 4.16.2
if ($installer->tableExists($this->getTable('custom_options_option_description')) && !$installer->tableExists($this->getTable('mageworx_custom_options_option_description'))) {
    $installer->run("RENAME TABLE {$this->getTable('custom_options_option_description')} TO {$this->getTable('mageworx_customoptions/option_description')};");
}

if ($installer->tableExists($this->getTable('custom_options_group')) && !$installer->tableExists($this->getTable('mageworx_custom_options_group'))) {
    $installer->run("RENAME TABLE {$this->getTable('custom_options_group')} TO {$this->getTable('mageworx_customoptions/group')};");
}

if ($installer->tableExists($this->getTable('custom_options_relation')) && !$installer->tableExists($this->getTable('mageworx_custom_options_relation'))) {
    $installer->run("RENAME TABLE {$this->getTable('custom_options_relation')} TO {$this->getTable('mageworx_customoptions/relation')};");
}

if ($installer->tableExists($this->getTable('custom_options_group_store')) && !$installer->tableExists($this->getTable('mageworx_custom_options_group_store'))) {
    $installer->run("RENAME TABLE {$this->getTable('custom_options_group_store')} TO {$this->getTable('mageworx_customoptions/group_store')};");
}

if ($installer->tableExists($this->getTable('custom_options_option_default')) && !$installer->tableExists($this->getTable('mageworx_custom_options_option_default'))) {
    $installer->run("RENAME TABLE {$this->getTable('custom_options_option_default')} TO {$this->getTable('mageworx_customoptions/option_default')};");
}

if ($installer->tableExists($this->getTable('custom_options_option_type_tier_price')) && !$installer->tableExists($this->getTable('mageworx_custom_options_option_type_tier_price'))) {
    $installer->run("RENAME TABLE {$this->getTable('custom_options_option_type_tier_price')} TO {$this->getTable('mageworx_customoptions/option_type_tier_price')};");
}

if ($installer->tableExists($this->getTable('custom_options_option_type_image')) && !$installer->tableExists($this->getTable('mageworx_custom_options_option_type_image'))) {
    $installer->run("RENAME TABLE {$this->getTable('custom_options_option_type_image')} TO {$this->getTable('mageworx_customoptions/option_type_image')};");
}

if ($installer->tableExists($this->getTable('custom_options_option_view_mode')) && !$installer->tableExists($this->getTable('mageworx_custom_options_option_view_mode'))) {
    $installer->run("RENAME TABLE {$this->getTable('custom_options_option_view_mode')} TO {$this->getTable('mageworx_customoptions/option_view_mode')};");
}

if ($installer->tableExists($this->getTable('custom_options_option_type_special_price')) && !$installer->tableExists($this->getTable('mageworx_custom_options_option_type_special_price'))) {
    $installer->run("RENAME TABLE {$this->getTable('custom_options_option_type_special_price')} TO {$this->getTable('mageworx_customoptions/option_type_special_price')};");
}

if ($installer->tableExists($this->getTable('custom_options_option_type_description')) && !$installer->tableExists($this->getTable('mageworx_custom_options_option_type_description'))) {
    $installer->run("RENAME TABLE {$this->getTable('custom_options_option_type_description')} TO {$this->getTable('mageworx_customoptions/option_type_description')};");
}


// 1.0.0
$installer->run("
-- DROP TABLE IF EXISTS {$installer->getTable('mageworx_customoptions/group')};
CREATE TABLE IF NOT EXISTS {$installer->getTable('mageworx_customoptions/group')} (
  `group_id` int(10) unsigned NOT NULL auto_increment,
  `title` varchar(255) NOT NULL default '',
  `is_active` tinyint(1) NOT NULL,
  `store_id` smallint(5) unsigned default NULL,
  `hash_options` longtext NOT NULL,
   PRIMARY KEY (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- DROP TABLE IF EXISTS {$installer->getTable('mageworx_customoptions/relation')};
CREATE TABLE IF NOT EXISTS {$installer->getTable('mageworx_customoptions/relation')} (
  `id` int(10) unsigned NOT NULL auto_increment,
  `group_id` int(10) unsigned NOT NULL,
  `product_id` int(10) unsigned NOT NULL,
  `option_id` int(10) unsigned NOT NULL,
   PRIMARY KEY (`id`),
   UNIQUE KEY `UNQ_MAGEWORX_CUSTOM_RELATION` (`group_id`,`option_id`,`product_id`),
   CONSTRAINT `FK_MAGEWORX_CUSTOM_OPTIONS_INDEX_PRODUCT_ENTITY` FOREIGN KEY (`product_id`) REFERENCES `{$installer->getTable('catalog/product')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
   CONSTRAINT `FK_MAGEWORX_CUSTOM_OPTIONS_INDEX_GROUP_RELATION` FOREIGN KEY (`group_id`) REFERENCES `{$installer->getTable('mageworx_customoptions/group')}` (`group_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->run("
-- DROP TABLE IF EXISTS {$installer->getTable('mageworx_customoptions/option_description')};
CREATE TABLE IF NOT EXISTS {$installer->getTable('mageworx_customoptions/option_description')} (
  `option_description_id` int(10) unsigned NOT NULL auto_increment,
  `option_id` int(10) unsigned NOT NULL default '0',
  `store_id` smallint(5) unsigned NOT NULL default '0',
  `description` text,
  PRIMARY KEY  (`option_description_id`),
  UNIQUE KEY `option_id+store_id` (`option_id`,`store_id`),
  KEY `store_id` (`store_id`),
  CONSTRAINT `FK_MAGEWORX_CUSTOM_OPTIONS_DESCRIPTION_OPTION` FOREIGN KEY (`option_id`) REFERENCES `{$installer->getTable('catalog/product_option')}` (`option_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_MAGEWORX_CUSTOM_OPTIONS_DESCRIPTION_STORE` FOREIGN KEY (`store_id`) REFERENCES `{$installer->getTable('core/store')}` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

$installer->run("-- DROP TABLE IF EXISTS `{$installer->getTable('mageworx_customoptions/group_store')}`;
CREATE TABLE IF NOT EXISTS `{$installer->getTable('mageworx_customoptions/group_store')}` (
  `group_store_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `group_id` int(10) unsigned NOT NULL,
  `store_id` smallint(5) unsigned NOT NULL,
  `hash_options` longtext NOT NULL,
  PRIMARY KEY (`group_store_id`),
  UNIQUE KEY `UNQ_CUSTOM_OPTIONS_GROUP_STORE` (`group_id`,`store_id`),
  CONSTRAINT `FK_MAGEWORX_CUSTOM_OPTIONS_GROUP_STORE` FOREIGN KEY (`group_id`) REFERENCES `{$installer->getTable('mageworx_customoptions/group')}` (`group_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

$installer->run("-- DROP TABLE IF EXISTS {$installer->getTable('mageworx_customoptions/option_default')};
CREATE TABLE IF NOT EXISTS `{$installer->getTable('mageworx_customoptions/option_default')}` (
  `option_default_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `option_id` int(10) unsigned NOT NULL DEFAULT '0',
  `store_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `default_text` text NOT NULL,
  PRIMARY KEY (`option_default_id`),
  UNIQUE KEY `option_id+store_id` (`option_id`,`store_id`),
  KEY `store_id` (`store_id`),
  CONSTRAINT `FK_MAGEWORX_CUSTOM_OPTIONS_DEFAULT_OPTION` FOREIGN KEY (`option_id`) REFERENCES `{$installer->getTable('catalog/product_option')}` (`option_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_MAGEWORX_CUSTOM_OPTIONS_DEFAULT_STORE` FOREIGN KEY (`store_id`) REFERENCES `{$installer->getTable('core/store')}` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

ALTER TABLE `{$installer->getTable('mageworx_customoptions/option_description')}` CHANGE `description` `description` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';
DELETE FROM `{$installer->getTable('mageworx_customoptions/option_description')}` WHERE `description` = '';");

$installer->run("
-- DROP TABLE IF EXISTS `{$installer->getTable('mageworx_customoptions/option_type_tier_price')}`;
CREATE TABLE IF NOT EXISTS `{$installer->getTable('mageworx_customoptions/option_type_tier_price')}` (
  `option_type_tier_price_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `option_type_price_id` int(10) unsigned NOT NULL DEFAULT '0',
  `qty` int(10) unsigned NOT NULL DEFAULT '0',
  `price` decimal(12,4) NOT NULL DEFAULT '0.0000',
  `price_type` enum('fixed','percent') NOT NULL DEFAULT 'fixed',
  PRIMARY KEY (`option_type_tier_price_id`),
  UNIQUE KEY `option_type_price_id+qty` (`option_type_price_id`,`qty`),
  CONSTRAINT `FK_MAGEWORX_CUSTOM_OPTIONS_INDEX_OPTION_TYPE_TIER_PRICE` FOREIGN KEY (`option_type_price_id`) REFERENCES `{$installer->getTable('catalog/product_option_type_price')}` (`option_type_price_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

-- DROP TABLE IF EXISTS `{$installer->getTable('mageworx_customoptions/option_type_image')}`;
CREATE TABLE IF NOT EXISTS `{$installer->getTable('mageworx_customoptions/option_type_image')}` (
  `option_type_image_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `option_type_id` int(10) unsigned NOT NULL DEFAULT '0',
  `image_file` varchar (255) default '',
  `sort_order` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `source` tinyint(3) NOT NULL DEFAULT '1' COMMENT '1-file,2-color,3-gallery',
  PRIMARY KEY (`option_type_image_id`),
  CONSTRAINT `FK_MAGEWORX_CUSTOM_OPTIONS_INDEX_OPTION_TYPE_IMAGE` FOREIGN KEY (`option_type_id`) REFERENCES `{$installer->getTable('catalog/product_option_type_value')}` (`option_type_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

-- DROP TABLE IF EXISTS `{$installer->getTable('mageworx_customoptions/option_view_mode')}`;
CREATE TABLE IF NOT EXISTS `{$installer->getTable('mageworx_customoptions/option_view_mode')}` (
  `view_mode_id` int(10) unsigned NOT NULL auto_increment,
  `option_id` int(10) unsigned NOT NULL default '0',
  `store_id` smallint(5) unsigned NOT NULL default '0',
  `view_mode` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY  (`view_mode_id`),
  UNIQUE KEY `option_id+store_id` (`option_id`,`store_id`),
  KEY `store_id` (`store_id`),
  CONSTRAINT `FK_MAGEWORX_CUSTOM_OPTIONS_VIEW_MODE_OPTION` FOREIGN KEY (`option_id`) REFERENCES `{$installer->getTable('catalog/product_option')}` (`option_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_MAGEWORX_CUSTOM_OPTIONS_VIEW_MODE_STORE` FOREIGN KEY (`store_id`) REFERENCES `{$installer->getTable('core/store')}` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
");

$installer->run("
-- DROP TABLE IF EXISTS `{$installer->getTable('mageworx_customoptions/option_type_special_price')}`;
CREATE TABLE IF NOT EXISTS `{$installer->getTable('mageworx_customoptions/option_type_special_price')}` (
  `option_type_special_price_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `option_type_price_id` int(10) unsigned NOT NULL DEFAULT '0',
  `customer_group_id` smallint(3) unsigned NOT NULL DEFAULT '32000' COMMENT '32000 - All Groups',
  `price` decimal(12,4) NOT NULL DEFAULT '0.0000',
  `price_type` enum('fixed','percent') NOT NULL DEFAULT 'fixed',
  `comment` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`option_type_special_price_id`),
  UNIQUE KEY `option_type_price_id+customer_group_id` (`option_type_price_id`,`customer_group_id`),
  CONSTRAINT `FK_MAGEWORX_CUSTOM_OPTIONS_INDEX_OPTION_TYPE_SPECIAL_PRICE` FOREIGN KEY (`option_type_price_id`) REFERENCES `{$installer->getTable('catalog/product_option_type_price')}` (`option_type_price_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;");

$installer->run("
-- DROP TABLE IF EXISTS {$installer->getTable('mageworx_customoptions/option_type_description')};
CREATE TABLE IF NOT EXISTS {$installer->getTable('mageworx_customoptions/option_type_description')} (
  `option_type_description_id` int(10) unsigned NOT NULL auto_increment,
  `option_type_id` int(10) unsigned NOT NULL default '0',
  `store_id` smallint(5) unsigned NOT NULL default '0',
  `description` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  PRIMARY KEY  (`option_type_description_id`),
  UNIQUE KEY `option_type_id+store_id` (`option_type_id`,`store_id`),
  KEY `store_id` (`store_id`),
  CONSTRAINT `FK_MAGEWORX_CUSTOM_OPTIONS_TYPE_DESCRIPTION_OPTION` FOREIGN KEY (`option_type_id`) REFERENCES `{$installer->getTable('catalog/product_option_type_value')}` (`option_type_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_MAGEWORX_CUSTOM_OPTIONS_TYPE_DESCRIPTION_STORE` FOREIGN KEY (`store_id`) REFERENCES `{$installer->getTable('core/store')}` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");


// 1.0.0
if (!$installer->getConnection()->tableColumnExists($installer->getTable('catalog/product_option'), 'customoptions_status')) {
    $installer->getConnection()->addColumn(
        $installer->getTable('catalog/product_option'),
        'customoptions_status',
        'tinyint(1) NOT NULL default 0'
    );
}

// 1.0.2 > 1.0.3
if (!$installer->getConnection()->tableColumnExists($installer->getTable('catalog/product_option_type_value'), 'customoptions_qty')) {
    $installer->getConnection()->addColumn(
        $installer->getTable('catalog/product_option_type_value'),
        'customoptions_qty',
        "varchar(10) NOT NULL default ''"
    );
}

// 1.0.3 > 1.0.4
if (!$installer->getConnection()->tableColumnExists($installer->getTable('catalog/product_option'), 'customoptions_is_onetime')) {
    $installer->getConnection()->addColumn(
        $installer->getTable('catalog/product_option'),
        'customoptions_is_onetime',
        'TINYINT (1) NOT NULL DEFAULT 0'
    );
}

// 1.0.4 > 1.1.0
if (!$installer->getConnection()->tableColumnExists($installer->getTable('catalog/product_option'), 'image_path')) {
    $installer->getConnection()->addColumn(
        $installer->getTable('catalog/product_option'),
        'image_path',
        "varchar (255) default ''"
    );
}

// 1.1.0 > 2.0.0
if (!$installer->getConnection()->tableColumnExists($installer->getTable('catalog/product_option_type_value'), 'default')) {
    $installer->getConnection()->addColumn(
        $installer->getTable('catalog/product_option_type_value'),
        'default',
        "tinyint(1) NOT NULL DEFAULT '0'"
    );
}

if (!$installer->getConnection()->tableColumnExists($installer->getTable('catalog/product_option'), 'customer_groups')) {
    $installer->getConnection()->addColumn(
        $installer->getTable('catalog/product_option'),
        'customer_groups',
        "varchar (255) default ''"
    );
}

// 2.0.4 > 2.1.0
if (!$installer->getConnection()->tableColumnExists($installer->getTable('catalog/product_option'), 'qnty_input')) {
    $installer->getConnection()->addColumn(
        $installer->getTable('catalog/product_option'),
        'qnty_input',
        "tinyint(1) NOT NULL DEFAULT '0'"
    );
}

// 2.1.7 > 2.1.8
if (!$installer->getConnection()->tableColumnExists($installer->getTable('catalog/product_option'), 'in_group_id')) {
    $installer->getConnection()->addColumn(
        $installer->getTable('catalog/product_option'),
        'in_group_id',
        "SMALLINT UNSIGNED NOT NULL DEFAULT '0'"
    );
}

if (!$installer->getConnection()->tableColumnExists($installer->getTable('catalog/product_option_type_value'), 'in_group_id')) {
    $installer->getConnection()->addColumn(
        $installer->getTable('catalog/product_option_type_value'),
        'in_group_id',
        "SMALLINT UNSIGNED NOT NULL DEFAULT '0'"
    );
}


if ($installer->getConnection()->tableColumnExists($installer->getTable('catalog/product_option'), 'customoptions_status')) {
    $installer->getConnection()->dropColumn(
        $installer->getTable('catalog/product_option'),
        'customoptions_status'
    );
}

// 2.1.8 > 2.1.9
$installer->getConnection()->addKey($installer->getTable('mageworx_customoptions/relation'), 'option_id', 'option_id');

// 2.1.99 > 2.2.0
$installer->run("ALTER TABLE `{$installer->getTable('catalog/product_option')}` CHANGE `in_group_id` `in_group_id` INT UNSIGNED NOT NULL DEFAULT '0'");
$installer->run("ALTER TABLE `{$installer->getTable('catalog/product_option_type_value')}` CHANGE `in_group_id` `in_group_id` INT UNSIGNED NOT NULL DEFAULT '0'");

if (!$installer->getConnection()->tableColumnExists($installer->getTable('catalog/product_option'), 'is_dependent')) {
    $installer->getConnection()->addColumn(
        $installer->getTable('catalog/product_option'),
        'is_dependent',
        "tinyint(1) NOT NULL DEFAULT '0'"
    );
}

if (!$installer->getConnection()->tableColumnExists($installer->getTable('catalog/product_option_type_value'), 'dependent_ids')) {
    $installer->getConnection()->addColumn(
        $installer->getTable('catalog/product_option_type_value'),
        'dependent_ids',
        "varchar(255) NOT NULL DEFAULT ''"
    );
}

$installer->run("UPDATE `{$installer->getTable('catalog/product_option')}` AS cpo, `{$installer->getTable('mageworx_customoptions/relation')}` AS cor
    SET cpo.`in_group_id`=(cor.`group_id` * 65535) + cpo.`in_group_id`
    WHERE cpo.`option_id`=cor.`option_id` AND cpo.`in_group_id`>0 AND cpo.`in_group_id` < 65536 AND cor.`group_id`>0 AND cor.`group_id` IS NOT NULL");

$installer->run("UPDATE `{$installer->getTable('catalog/product_option_type_value')}` AS cpotv, `{$installer->getTable('mageworx_customoptions/relation')}` AS cor
    SET cpotv.`in_group_id`=(cor.`group_id` * 65535) + cpotv.`in_group_id`
    WHERE cpotv.`option_id`=cor.`option_id` AND cpotv.`in_group_id`>0 AND cpotv.`in_group_id` < 65536 AND cor.`group_id`>0 AND cor.`group_id` IS NOT NULL");

// 2.2.5 > 2.2.6
$installer->run("ALTER TABLE `{$installer->getTable('catalog/product_option_type_value')}` CHANGE `dependent_ids` `dependent_ids` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''");

// 2.4.4 > 2.2.5
$installer->run("UPDATE IGNORE `{$this->getTable('core_config_data')}` SET `path` = REPLACE(`path`,'mageworx_sales/','mageworx_catalog/') WHERE `path` LIKE 'mageworx_sales/customoptions/%'");

// Update config path in case of migration to 4.16.2 version
$installer->run("UPDATE IGNORE `{$this->getTable('core_config_data')}` SET `path` = REPLACE(`path`,'mageworx_catalog/customoptions/','mageworx_customoptions/main/') WHERE `path` LIKE 'mageworx_catalog/customoptions/%'");

// 2.5.0 > 2.6.0
if ($installer->getConnection()->tableColumnExists($installer->getTable('mageworx_customoptions/group'), 'store_id')) {
    $installer->run("ALTER TABLE `{$installer->getTable('mageworx_customoptions/group')}` DROP `store_id`;");
}

// 2.99.0 > 3.0.0
if (!$installer->getConnection()->tableColumnExists($installer->getTable('catalog/product_option'), 'div_class')) {
    $installer->getConnection()->addColumn(
        $installer->getTable('catalog/product_option'),
        'div_class',
        "varchar(64) NOT NULL default ''"
    );
}

if (!$installer->getConnection()->tableColumnExists($installer->getTable('catalog/product_option_type_value'), 'weight')) {
    $installer->getConnection()->addColumn(
        $installer->getTable('catalog/product_option_type_value'),
        'weight',
        "DECIMAL( 12, 4 ) NOT NULL DEFAULT '0'"
    );
}

if (!$installer->getConnection()->tableColumnExists($installer->getTable('catalog/product'), 'absolute_weight')) {
    $installer->getConnection()->addColumn(
        $installer->getTable('catalog/product'),
        'absolute_price',
        "TINYINT (1) NOT NULL DEFAULT 0"
    );

    $installer->getConnection()->addColumn(
        $installer->getTable('catalog/product'),
        'absolute_weight',
        "TINYINT (1) NOT NULL DEFAULT 0"
    );
}


if (!$installer->getConnection()->tableColumnExists($installer->getTable('mageworx_customoptions/group'), 'absolute_weight')) {
    $installer->getConnection()->addColumn(
        $installer->getTable('mageworx_customoptions/group'),
        'absolute_price',
        "TINYINT (1) NOT NULL DEFAULT 0"
    );

    $installer->getConnection()->addColumn(
        $installer->getTable('mageworx_customoptions/group'),
        'absolute_weight',
        "TINYINT (1) NOT NULL DEFAULT 0"
    );
}

// 3.9.99 > 4.0.0
if (!$installer->getConnection()->tableColumnExists($installer->getTable('catalog/product_option'), 'view_mode') && $installer->getConnection()->tableColumnExists($installer->getTable('catalog/product_option'), 'is_enabled')) {
    $installer->run("ALTER TABLE `{$installer->getTable('catalog/product_option')}` CHANGE `is_enabled` `view_mode` TINYINT(1) NOT NULL DEFAULT '1';");
}

if ($installer->getConnection()->tableColumnExists($installer->getTable('catalog/product_option'), 'view_mode')) {
    $installer->run("INSERT IGNORE INTO `{$installer->getTable('mageworx_customoptions/option_view_mode')}` (`option_id`, `store_id`, `view_mode`) SELECT  `option_id`, 0 AS `store_id`, `view_mode` FROM  `{$installer->getTable('catalog/product_option')}`;");
    $installer->getConnection()->dropColumn(
        $installer->getTable('catalog/product_option'),
        'view_mode'
    );
}

if (!$installer->getConnection()->tableColumnExists($installer->getTable('catalog/product_option'), 'sku_policy')) {
    $installer->getConnection()->addColumn(
        $installer->getTable('catalog/product_option'),
        'sku_policy',
        "TINYINT( 1 ) NOT NULL DEFAULT '0'"
    );
}

if (!$installer->getConnection()->tableColumnExists($installer->getTable('catalog/product_option'), 'image_mode')) {
    $installer->getConnection()->addColumn(
        $installer->getTable('catalog/product_option'),
        'image_mode',
        "TINYINT(1) NOT NULL DEFAULT '1'"
    );

    $installer->getConnection()->addColumn(
        $installer->getTable('catalog/product_option'),
        'exclude_first_image',
        "TINYINT(1) NOT NULL DEFAULT '0'"
    );
}

// fill image table
if ($installer->getConnection()->tableColumnExists($installer->getTable('catalog/product_option_type_value'), 'image_path')) {
    $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
    $helper = Mage::helper('mageworx_customoptions');
    $select = $connection->select()->from($installer->getTable('catalog/product_option_type_value'), array('option_type_id', 'image_path'))->where("image_path <> '' AND image_path IS NOT NULL");
    $allOptionValueImages = $connection->fetchAll($select);

    $installer->run('LOCK TABLES '. $connection->quoteIdentifier($installer->getTable('mageworx_customoptions/option_type_image'), true) .' WRITE;');
    foreach($allOptionValueImages as $opValImg) {
        $result = $helper->getCheckImgPath($opValImg['image_path']);
        if ($result) {
            list($imagePath, $fileName) = $result;
            $imageFile = $imagePath . $fileName;
            $connection->insert($installer->getTable('mageworx_customoptions/option_type_image'), array('option_type_id'=>$opValImg['option_type_id'],'image_file'=>$imageFile));
        }
    }
    $installer->run('UNLOCK TABLES;');

    $installer->getConnection()->dropColumn($installer->getTable('catalog/product_option_type_value'), 'image_path');
}


// 4.3.00 > 4.3.1
if (!$installer->getConnection()->tableColumnExists($installer->getTable('catalog/product'), 'sku_policy')) {
    $installer->getConnection()->addColumn(
        $installer->getTable('catalog/product'),
        'sku_policy',
        "TINYINT (1) NOT NULL DEFAULT 0"
    );

    $installer->run("
        UPDATE `{$installer->getTable('catalog_product_entity')}` AS t1 SET t1.`sku_policy` = 3 WHERE (SELECT COUNT(*) FROM `{$installer->getTable('catalog_product_option')}` WHERE `product_id` = t1.`entity_id` AND `sku_policy` = 3) > 0;
        UPDATE `{$installer->getTable('catalog_product_option')}` SET `sku_policy` = 0 WHERE `sku_policy` = 3;
    ");
}

if (!$installer->getConnection()->tableColumnExists($installer->getTable('mageworx_customoptions/group'), 'sku_policy')) {
    $installer->getConnection()->addColumn(
        $installer->getTable('mageworx_customoptions/group'),
        'sku_policy',
        "TINYINT (1) NOT NULL DEFAULT 0"
    );
}

// 4.4.6 > 4.4.7
if (!$installer->getConnection()->tableColumnExists($installer->getTable('mageworx_customoptions/group'), 'update_inventory')) {
    $installer->getConnection()->addColumn(
        $installer->getTable('mageworx_customoptions/group'),
        'update_inventory',
        "TINYINT (1) NOT NULL DEFAULT 0"
    );
}

$installer->run("UPDATE IGNORE `{$this->getTable('core_config_data')}` SET `path` = REPLACE(`path`,'mageworx_customoptions/main/option_sku_price_linking_enabled','mageworx_customoptions/main/assigned_product_attributes') WHERE `path` LIKE 'mageworx_customoptions/main/%'");

// 4.5.9 > 4.6.0
if (!$installer->getConnection()->tableColumnExists($installer->getTable('catalog/product_option_type_value'), 'cost')) {
    $installer->getConnection()->addColumn(
        $installer->getTable('catalog/product_option_type_value'),
        'cost',
        "DECIMAL(12, 4) NOT NULL DEFAULT '0'"
    );
}

// 4.6.5 > 4.7.0
if ($installer->getConnection()->tableColumnExists($installer->getTable('catalog/product_option_type_price'), 'special_price')) {
    $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
    $select = $connection->select()->from($installer->getTable('catalog/product_option_type_price'), array('option_type_price_id', 'price_type', 'special_price', 'special_comment'))->where("special_price IS NOT NULL AND special_price > 0");
    $allSpecialPrices = $connection->fetchAll($select);

    $installer->run('LOCK TABLES '. $connection->quoteIdentifier($installer->getTable('mageworx_customoptions/option_type_special_price'), true) .' WRITE;');
    foreach($allSpecialPrices as $value) {
        $connection->insert(
            $installer->getTable('mageworx_customoptions/option_type_special_price'),
            array('option_type_price_id'=>$value['option_type_price_id'], 'price'=>$value['special_price'], 'price_type'=>$value['price_type'], 'comment'=>$value['special_comment'])
        );
    }
    $installer->run('UNLOCK TABLES;');

    $installer->getConnection()->dropColumn($installer->getTable('catalog/product_option_type_price'), 'special_comment');
    $installer->getConnection()->dropColumn($installer->getTable('catalog/product_option_type_price'), 'special_price');
}


if (!$installer->getConnection()->tableColumnExists($installer->getTable('mageworx_customoptions/option_type_tier_price'), 'customer_group_id')) {
    $installer->getConnection()->addColumn(
        $installer->getTable('mageworx_customoptions/option_type_tier_price'),
        'customer_group_id',
        "smallint(3) unsigned NOT NULL DEFAULT '32000' COMMENT '32000 - All Groups' AFTER `option_type_price_id`"
    );

    $installer->run("ALTER TABLE `{$installer->getTable('mageworx_customoptions/option_type_tier_price')}`
        DROP INDEX `option_type_price_id+qty`,
        ADD UNIQUE `option_type_price_id+customer_group_id+qty` (`option_type_price_id` , `customer_group_id`, `qty`);");
}

// 4.7.0 > 4.7.1
if (!$installer->getConnection()->tableColumnExists($installer->getTable('mageworx_customoptions/group'), 'only_update')) {
    $installer->getConnection()->addColumn(
        $installer->getTable('mageworx_customoptions/group'),
        'only_update',
        "TINYINT (1) NOT NULL DEFAULT 0"
    );
}

// 4.8.15 > 4.9.0
if (!$installer->getConnection()->tableColumnExists($installer->getTable('mageworx_customoptions/option_type_special_price'), 'date_from')) {
    $installer->getConnection()->addColumn(
        $installer->getTable('mageworx_customoptions/option_type_special_price'),
        'date_from',
        "date NULL DEFAULT NULL"
    );
    $installer->getConnection()->addColumn(
        $installer->getTable('mageworx_customoptions/option_type_special_price'),
        'date_to',
        "date NULL DEFAULT NULL"
    );
}

// 4.10.9 > 4.11.0
// view_mode = hidden => to type = 'hidden';
$installer->run("UPDATE `{$installer->getTable('catalog_product_option')}` AS option_tbl,
    `{$installer->getTable('mageworx_customoptions/option_view_mode')}` AS view_mode_tbl
    SET option_tbl.`type` = 'hidden', view_mode_tbl.`view_mode` = 1
    WHERE option_tbl.`type` IN ('drop_down', 'drop_down', 'multiple', 'radio', 'checkbox', 'swatch', 'multiswatch')
    AND option_tbl.`option_id` = view_mode_tbl.`option_id` AND view_mode_tbl.`view_mode` = 2;");


// fix and clean up the debris of tables whith options
$installer->run("
    DELETE t1 FROM `{$installer->getTable('catalog_product_option')}` AS t1 WHERE (SELECT COUNT(*) FROM `{$installer->getTable('catalog_product_entity')}` WHERE `entity_id` = t1.`product_id`) = 0;
    DELETE t1 FROM `{$installer->getTable('catalog_product_option_title')}` AS t1 WHERE (SELECT COUNT(*) FROM `{$installer->getTable('catalog_product_option')}` WHERE `option_id` = t1.`option_id`) = 0;
    DELETE t1 FROM `{$installer->getTable('catalog_product_option_price')}` AS t1 WHERE (SELECT COUNT(*) FROM `{$installer->getTable('catalog_product_option')}` WHERE `option_id` = t1.`option_id`) = 0;
    DELETE t1 FROM `{$installer->getTable('catalog_product_option_type_value')}` AS t1 WHERE (SELECT COUNT(*) FROM `{$installer->getTable('catalog_product_option')}` WHERE `option_id` = t1.`option_id`) = 0;
    DELETE t1 FROM `{$installer->getTable('catalog_product_option_type_title')}` AS t1 WHERE (SELECT COUNT(*) FROM `{$installer->getTable('catalog_product_option_type_value')}` WHERE `option_type_id` = t1.`option_type_id`) = 0;
    DELETE t1 FROM `{$installer->getTable('catalog_product_option_type_price')}` AS t1 WHERE (SELECT COUNT(*) FROM `{$installer->getTable('catalog_product_option_type_value')}` WHERE `option_type_id` = t1.`option_type_id`) = 0;
    DELETE t1 FROM `{$installer->getTable('mageworx_customoptions/option_type_image')}` AS t1 WHERE (SELECT COUNT(*) FROM `{$installer->getTable('catalog_product_option_type_value')}` WHERE `option_type_id` = t1.`option_type_id`) = 0;
    DELETE t1 FROM `{$installer->getTable('mageworx_customoptions/relation')}` AS t1 WHERE (SELECT COUNT(*) FROM `{$installer->getTable('catalog_product_option')}` WHERE `option_id` = t1.`option_id`) = 0;
    DELETE t1 FROM `{$installer->getTable('mageworx_customoptions/option_view_mode')}` AS t1 WHERE (SELECT COUNT(*) FROM `{$installer->getTable('catalog_product_option')}` WHERE `option_id` = t1.`option_id`) = 0;
    DELETE t1 FROM `{$installer->getTable('mageworx_customoptions/option_description')}` AS t1 WHERE (SELECT COUNT(*) FROM `{$installer->getTable('catalog_product_option')}` WHERE `option_id` = t1.`option_id`) = 0;
    DELETE t1 FROM `{$installer->getTable('mageworx_customoptions/option_default')}` AS t1 WHERE (SELECT COUNT(*) FROM `{$installer->getTable('catalog_product_option')}` WHERE `option_id` = t1.`option_id`) = 0;
    DELETE t1 FROM `{$installer->getTable('mageworx_customoptions/option_type_tier_price')}` AS t1 WHERE (SELECT COUNT(*) FROM `{$installer->getTable('catalog_product_option_type_price')}` WHERE `option_type_price_id` = t1.`option_type_price_id`) = 0;
    DELETE t1 FROM `{$installer->getTable('mageworx_customoptions/option_type_special_price')}` AS t1 WHERE (SELECT COUNT(*) FROM `{$installer->getTable('catalog_product_option_type_price')}` WHERE `option_type_price_id` = t1.`option_type_price_id`) = 0;
");


// set Enable Option Description = Yes
$connection = Mage::getSingleton('core/resource')->getConnection('core_write');
$select = $connection->select()->from($installer->getTable('mageworx_customoptions/option_description'), array('COUNT(*)'));
$descriptions = $connection->fetchOne($select);
if ($descriptions>0) {
    $installer->run("INSERT IGNORE INTO `{$installer->getTable('core_config_data')}` (`path`, `value`) VALUES ('mageworx_customoptions/main/option_description_enabled', 1);");
}

// set Link Assigned Product's Attributes to Option via SKU = Qty
$select = $connection->select()->from($installer->getTable('core_config_data'), array('value'))->where("`path` = 'mageworx_customoptions/main/assigned_product_attributes'");
$assigned = $connection->fetchOne($select);
if ($assigned!==false) {
    $assigned = explode(',', $assigned);
    if (!in_array('5', $assigned)) $assigned[] = 5;
    $assigned = implode(',', $assigned);
    $installer->run("UPDATE IGNORE `{$this->getTable('core_config_data')}` SET `value` = '". $assigned ."' WHERE `path` = 'mageworx_customoptions/main/assigned_product_attributes'");
}

// 4.12.4.1 > 4.12.4.2
$installer->getConnection()->addColumn(
    $installer->getTable('catalog_product_option_type_value'),
    'descr',
    "varchar (255) default ''"
);

// 4.12.5 > 4.13.0
// view_mode = hidden => to type = 'hidden';
$installer->run("UPDATE `{$installer->getTable('catalog_product_option')}` AS option_tbl,
    `{$installer->getTable('mageworx_customoptions/option_view_mode')}` AS view_mode_tbl
    SET option_tbl.`type` = 'hidden', view_mode_tbl.`view_mode` = 1
    WHERE option_tbl.`type` IN ('drop_down', 'drop_down', 'multiple', 'radio', 'checkbox', 'swatch', 'multiswatch')
    AND option_tbl.`option_id` = view_mode_tbl.`option_id` AND view_mode_tbl.`view_mode` = 2;");


// fix and clean up the debris of tables whith options
$installer->run("
    DELETE t1 FROM `{$installer->getTable('catalog_product_option')}` AS t1 WHERE (SELECT COUNT(*) FROM `{$installer->getTable('catalog_product_entity')}` WHERE `entity_id` = t1.`product_id`) = 0;
    DELETE t1 FROM `{$installer->getTable('catalog_product_option_title')}` AS t1 WHERE (SELECT COUNT(*) FROM `{$installer->getTable('catalog_product_option')}` WHERE `option_id` = t1.`option_id`) = 0;
    DELETE t1 FROM `{$installer->getTable('catalog_product_option_price')}` AS t1 WHERE (SELECT COUNT(*) FROM `{$installer->getTable('catalog_product_option')}` WHERE `option_id` = t1.`option_id`) = 0;
    DELETE t1 FROM `{$installer->getTable('catalog_product_option_type_value')}` AS t1 WHERE (SELECT COUNT(*) FROM `{$installer->getTable('catalog_product_option')}` WHERE `option_id` = t1.`option_id`) = 0;
    DELETE t1 FROM `{$installer->getTable('catalog_product_option_type_title')}` AS t1 WHERE (SELECT COUNT(*) FROM `{$installer->getTable('catalog_product_option_type_value')}` WHERE `option_type_id` = t1.`option_type_id`) = 0;
    DELETE t1 FROM `{$installer->getTable('catalog_product_option_type_price')}` AS t1 WHERE (SELECT COUNT(*) FROM `{$installer->getTable('catalog_product_option_type_value')}` WHERE `option_type_id` = t1.`option_type_id`) = 0;
    DELETE t1 FROM `{$installer->getTable('mageworx_customoptions/option_type_image')}` AS t1 WHERE (SELECT COUNT(*) FROM `{$installer->getTable('catalog_product_option_type_value')}` WHERE `option_type_id` = t1.`option_type_id`) = 0;
    DELETE t1 FROM `{$installer->getTable('mageworx_customoptions/relation')}` AS t1 WHERE (SELECT COUNT(*) FROM `{$installer->getTable('catalog_product_option')}` WHERE `option_id` = t1.`option_id`) = 0;
    DELETE t1 FROM `{$installer->getTable('mageworx_customoptions/option_view_mode')}` AS t1 WHERE (SELECT COUNT(*) FROM `{$installer->getTable('catalog_product_option')}` WHERE `option_id` = t1.`option_id`) = 0;
    DELETE t1 FROM `{$installer->getTable('mageworx_customoptions/option_description')}` AS t1 WHERE (SELECT COUNT(*) FROM `{$installer->getTable('catalog_product_option')}` WHERE `option_id` = t1.`option_id`) = 0;
    DELETE t1 FROM `{$installer->getTable('mageworx_customoptions/option_default')}` AS t1 WHERE (SELECT COUNT(*) FROM `{$installer->getTable('catalog_product_option')}` WHERE `option_id` = t1.`option_id`) = 0;
    DELETE t1 FROM `{$installer->getTable('mageworx_customoptions/option_type_tier_price')}` AS t1 WHERE (SELECT COUNT(*) FROM `{$installer->getTable('catalog_product_option_type_price')}` WHERE `option_type_price_id` = t1.`option_type_price_id`) = 0;
    DELETE t1 FROM `{$installer->getTable('mageworx_customoptions/option_type_special_price')}` AS t1 WHERE (SELECT COUNT(*) FROM `{$installer->getTable('catalog_product_option_type_price')}` WHERE `option_type_price_id` = t1.`option_type_price_id`) = 0;
");


// set Enable Option Description = Yes
$connection = Mage::getSingleton('core/resource')->getConnection('core_write');
$select = $connection->select()->from($installer->getTable('mageworx_customoptions/option_description'), array('COUNT(*)'));
$descriptions = $connection->fetchOne($select);
if ($descriptions>0) {
    $installer->run("INSERT IGNORE INTO `{$installer->getTable('core_config_data')}` (`path`, `value`) VALUES ('mageworx_customoptions/main/option_description_enabled', 1);");
}

// set Enable Option Description = Tooltip
$select = $connection->select()->from($installer->getTable('core_config_data'), array('value'))->where("`path` = 'mageworx_customoptions/main/description_appearance'");
$appearance = $connection->fetchOne($select);
if ($appearance==2) {
    $installer->run("UPDATE `{$installer->getTable('core_config_data')}` SET `value` = 2 WHERE `path` = 'mageworx_customoptions/main/option_description_enabled';");
    $installer->run("DELETE FROM `{$installer->getTable('core_config_data')}` WHERE `path` = 'mageworx_customoptions/main/description_appearance';");
}

// set Link Assigned Product's Attributes to Option via SKU = Qty
$select = $connection->select()->from($installer->getTable('core_config_data'), array('value'))->where("`path` = 'mageworx_customoptions/main/assigned_product_attributes'");
$assigned = $connection->fetchOne($select);
if ($assigned!==false) {
    $assigned = explode(',', $assigned);
    if (!in_array('5', $assigned)) $assigned[] = 5;
    $assigned = implode(',', $assigned);
    $installer->run("UPDATE IGNORE `{$this->getTable('core_config_data')}` SET `value` = '". $assigned ."' WHERE `path` = 'mageworx_customoptions/main/assigned_product_attributes'");
}

// 4.13.9 > 4.14.0
if (!$installer->getConnection()->tableColumnExists($installer->getTable('catalog/product_option'), 'store_views')) {
    $installer->getConnection()->addColumn(
        $installer->getTable('catalog/product_option'),
        'store_views',
        "varchar (255) default ''"
    );
}

// 4.15.0 > 4.16.0
$table = $installer->getTable('catalog/product_option_type_value');

if (!$installer->getConnection()->tableColumnExists($table, 'extra')) {
    $installer->getConnection()->addColumn(
        $table,
        'extra',
        "varchar(10) NOT NULL DEFAULT ''"
    );
}

if ($installer->getConnection()->tableColumnExists($table, 'customoptions_qty')) {
    $installer->getConnection()->query("UPDATE {$table} set extra = customoptions_qty WHERE (customoptions_qty LIKE'%x%' OR customoptions_qty LIKE'%i%' OR customoptions_qty LIKE'%l%')");
    $installer->getConnection()->query("ALTER TABLE {$table} MODIFY COLUMN customoptions_qty varchar(12) NULL DEFAULT NULL");
    $installer->getConnection()->query("UPDATE {$table} set customoptions_qty = NULL WHERE (customoptions_qty LIKE'%x%' OR customoptions_qty LIKE'%i%' OR customoptions_qty LIKE'%l%' OR customoptions_qty='')");
    $installer->getConnection()->query("ALTER TABLE {$table} MODIFY COLUMN customoptions_qty decimal(12,4) NULL DEFAULT NULL");
}

if (!$installer->getConnection()->tableColumnExists($table, 'customoptions_min_qty')) {
    $installer->getConnection()->addColumn(
        $table,
        'customoptions_min_qty',
        "decimal(12,4) NULL DEFAULT NULL"
    );
}

if (!$installer->getConnection()->tableColumnExists($table, 'customoptions_max_qty')) {
    $installer->getConnection()->addColumn(
        $table,
        'customoptions_max_qty',
        "decimal(12,4) NULL DEFAULT NULL"
    );
}


$installer->endSetup();