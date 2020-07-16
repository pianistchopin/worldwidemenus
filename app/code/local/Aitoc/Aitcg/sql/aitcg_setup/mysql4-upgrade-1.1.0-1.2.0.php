<?php

$installer = $this;

$installer->startSetup();

$installer->run("
-- DROP TABLE IF EXISTS `{$this->getTable('aitcg/category')}`;
CREATE TABLE IF NOT EXISTS `{$this->getTable('aitcg/category')}` (
`category_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`name` VARCHAR( 80 ) NOT NULL ,
`description` TEXT NOT NULL
) ENGINE = INNODB ;
");


$installer->run("
-- DROP TABLE IF EXISTS `{$this->getTable('aitcg/category_image')}`;
CREATE TABLE IF NOT EXISTS `{$this->getTable('aitcg/category_image')}` (
`category_image_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`category_id` INT NOT NULL ,
`name` VARCHAR( 80 ) NOT NULL ,
`filename` VARCHAR( 255 ) NOT NULL ,
INDEX ( `category_id` )
) ENGINE = INNODB ;
");


$installer->run("
ALTER TABLE {$this->getTable('catalog/product_option_aitimage')} ADD `use_user_image` TINYINT( 1 ) DEFAULT '1';
");

$installer->run("
ALTER TABLE {$this->getTable('catalog/product_option_aitimage')} ADD `use_predefined_image` TINYINT( 1 ) DEFAULT '0';
");

$installer->run("
ALTER TABLE {$this->getTable('catalog/product_option_aitimage')} ADD `predefined_cats` VARCHAR( 255 );
");

$installer->endSetup(); 