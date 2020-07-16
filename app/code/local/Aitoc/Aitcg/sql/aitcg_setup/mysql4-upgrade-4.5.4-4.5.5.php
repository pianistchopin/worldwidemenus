<?php
$installer = $this;
$installer->startSetup();
$installer->getConnection()
    ->addColumn(
        $installer->getTable('catalog/product_option'),
        'option_for_mask',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_INTEGER,
            'unsigned' => true,
            'length'   => 10,
            'nullable' => true,
            'default'  => null,
            'comment'  => 'Option For Mask'
        )
    );

$installer->endSetup();
