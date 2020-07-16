<?php


$installer = $this;

$installer->startSetup();

$installer->run("
ALTER TABLE {$this->getTable('aitcg/color_set')} ADD `label` TEXT NOT NULL;
");

$installer->endSetup();