<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup  */
$installer->startSetup();

$installer->run("
    ALTER TABLE `{$this->getTable('aitcg/category')}` ADD `store_labels` TEXT NOT NULL;
    ALTER TABLE `{$this->getTable('aitcg/mask_category')}` ADD `store_labels` TEXT NOT NULL;
");

$installer->endSetup(); 