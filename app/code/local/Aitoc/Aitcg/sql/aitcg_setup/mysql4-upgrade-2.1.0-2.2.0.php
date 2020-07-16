<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup  */
$installer->startSetup();

$installer->run("
-- DROP TABLE IF EXISTS `{$this->getTable('aitcg/color_set')}`;
CREATE TABLE IF NOT EXISTS `{$this->getTable('aitcg/color_set')}` (
  `color_set_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `value` varchar(300) NOT NULL,
  `status` tinyint(1) NOT NULL,
  PRIMARY KEY (`color_set_id`),
  KEY `name` (`name`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
INSERT INTO `{$this->getTable('aitcg/color_set')}` (`name`, `value`, `status`) VALUES ('Default', '#FF0000#00FFFF#0000FF#0000A0#ADD8E6#800080#FFFF00#00FF00#FF00FF#FFFFFF#C0C0C0#808080#000000#FFA500#A52A2A#800000#008000#808000','1');
");



$installer->run("
    ALTER TABLE {$this->getTable('catalog/product_option_aitimage')} ADD `allow_text_distortion` TINYINT( 1 );
");

$installer->run("
    ALTER TABLE {$this->getTable('catalog/product_option_aitimage')} ADD `allow_predefined_colors` TINYINT( 1 );
");

$installer->run("
    ALTER TABLE {$this->getTable('catalog/product_option_aitimage')} ADD `color_set_id` INT(11) DEFAULT '1';
");

$installer->endSetup(); 