<?php


$installer = $this;

$installer->startSetup();

$installer->run("
ALTER TABLE {$this->getTable('catalog/product_option_aitimage')} ADD `use_digital_image` TINYINT( 1 ) DEFAULT '1';
");

$installer->endSetup();