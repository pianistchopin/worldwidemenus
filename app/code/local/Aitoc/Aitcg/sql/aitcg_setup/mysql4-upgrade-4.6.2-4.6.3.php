<?php
$installer = $this;

$installer->startSetup();

$installer->run("
    ALTER TABLE {$this->getTable('aitcg/category_image')} ADD `embossfilename` VARCHAR(300)  DEFAULT NULL;
");

$installer->endSetup();
