<?php

$installer = $this;
$installer->startSetup();

// 4.16.3 > 4.17.0
if (!$installer->getConnection()->tableColumnExists($installer->getTable('catalog/product_option'), 'show_swatch_title')) {
    $installer->getConnection()->addColumn(
        $installer->getTable('catalog/product_option'),
        'show_swatch_title',
        "tinyint(1) NOT NULL DEFAULT '0'"
    );
}

$installer->endSetup();