<?php

$installer = $this;

$installer->startSetup();

$installer->run("
UPDATE {$this->getTable('catalog/product_option')} SET `type` = 'file' WHERE `type`='aitcustomer_image';
");

$installer->run("
ALTER TABLE {$this->getTable('catalog/product_option')} DROP `preview`;
");
$installer->run("
ALTER TABLE {$this->getTable('catalog/product_option')} DROP `cpp_option_id`;
");
$installer->endSetup(); 