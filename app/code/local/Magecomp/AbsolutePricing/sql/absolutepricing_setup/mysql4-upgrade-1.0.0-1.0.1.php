<?php
$installer = $this;
$installer->startSetup();
$installer->run("
ALTER TABLE `{$this->getTable('catalog_product_option_type_price')}` CHANGE `price_type` `price_type` ENUM( 'fixed', 'percent', 'absolute', 'absoluteonce' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'fixed' 
");
$installer->endSetup();