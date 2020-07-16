<?php

$installer = $this;

$installer->startSetup();

$installer->run("
ALTER TABLE {$this->getTable('catalog/product_option_aitimage')} ADD `text_length` INT( 11 ) DEFAULT '80';
");

$installer->run("
ALTER TABLE {$this->getTable('catalog/product_option_aitimage')} ADD `allow_colorpick` TINYINT( 1 ) DEFAULT '1';
");

$installer->endSetup(); 