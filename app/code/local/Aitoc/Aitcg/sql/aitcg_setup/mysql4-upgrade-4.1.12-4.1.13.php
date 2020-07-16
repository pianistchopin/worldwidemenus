<?php
$installer = $this;
$installer->startSetup();

$installer->run("
    ALTER TABLE {$this->getTable('catalog/product_option_aitimage')} ADD `def_img_behind_text` TINYINT( 1 ) DEFAULT NULL;
    ALTER TABLE {$this->getTable('catalog/product_option_aitimage')} ADD `def_img_behind_image` TINYINT( 1 ) DEFAULT NULL;
    ALTER TABLE {$this->getTable('catalog/product_option_aitimage')} ADD `def_img_behind_clip` TINYINT( 1 ) DEFAULT NULL;
    ALTER TABLE {$this->getTable('catalog/product_option_aitimage')} ADD `allow_save_graphics` TINYINT( 1 ) DEFAULT NULL;
");

$installer->endSetup();