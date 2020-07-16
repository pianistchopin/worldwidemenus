<?php

$installer = $this;

$installer->startSetup();

$installer->run("
UPDATE {$this->getTable('catalog_product_option')} 
    SET `type` = 'aitcustomer_image' 
    WHERE `type`='file' AND option_id in (SELECT option_id FROM {$this->getTable('catalog_product_option_aitimage')});
");


$installer->endSetup(); 
