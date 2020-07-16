<?php
$installer = $this;
$installer->startSetup();
$installer->getConnection()
    ->addColumn(
        $installer->getTable('catalog/product_option_type_value'),
        'mask_category',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_INTEGER,
            'unsigned' => true,
            'length'   => 10,
            'nullable' => true,
            'default'  => null,
            'comment'  => 'Mask Category Id'
        )
    );

$installer->endSetup();
