<?php

$installer = $this;

$installer->startSetup();

$installer->run("

ALTER TABLE `{$this->getTable('adjicon/attribute')}` 
ADD `show_images` TINYINT( 1 ) NOT NULL AFTER `pos` ,
ADD `columns_num` TINYINT( 1 ) NOT NULL AFTER `show_images` ,
ADD `hide_qty`    TINYINT( 1 ) NOT NULL AFTER `columns_num` ,
ADD `sort_by`     TINYINT( 1 ) NOT NULL AFTER `hide_qty` ;

");

$installer->endSetup(); 