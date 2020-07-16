<?php

$installer = $this;
$installer->startSetup();

// 4.17.2 > 4.17.3
if (!$installer->getConnection()->tableColumnExists($installer->getTable('catalog/product_option'), 'upc')) {
    $installer->getConnection()->addColumn(
        $installer->getTable('catalog/product_option_type_value'),
        'upc',
        "varchar(40) NOT NULL default ''"
    );
}

if (!$installer->getConnection()->tableColumnExists($installer->getTable('mageworx_customoptions/group'), 'updated_at')) {
    $installer->getConnection()->addColumn(
        $installer->getTable('mageworx_customoptions/group'),
        'updated_at',
        "timestamp NULL"
    );
}

$installer->endSetup();