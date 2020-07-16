<?php
$installer = $this;

$installer->startSetup();

$installer->run("

CREATE TABLE `{$this->getTable('adjicon/icon')}` (
  `icon_id` mediumint(8) unsigned NOT NULL auto_increment,
  `option_id` mediumint(8) unsigned NOT NULL,
  `filename` varchar(255) NOT NULL,
  PRIMARY KEY  (`icon_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `{$this->getTable('adjicon/attribute')}` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `attribute_id` mediumint(8) unsigned NOT NULL,
  `pos` tinyint(3) unsigned NOT NULL,
  `attribute_code` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `attribute_id` (`attribute_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

$installer->endSetup(); 