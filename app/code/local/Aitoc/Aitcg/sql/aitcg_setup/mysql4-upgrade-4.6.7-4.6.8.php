<?php


$installer = $this;

$installer->startSetup();

$installer->run("
ALTER TABLE {$this->getTable('catalog/product_option_aitimage')} ADD `spread_type` VARCHAR(50)  DEFAULT 'single';
");

$installer->endSetup();