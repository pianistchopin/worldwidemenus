<?php
$installer = $this;
$installer->startSetup();
$installer->getConnection()
    ->addColumn(
        $installer->getTable('catalog/product_option'),
        'cpp_option_id',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_INTEGER,
            'unsigned' => true,
            'length'   => 10,
            'nullable' => true,
            'default'  => null,
            'comment'  => 'Cpp Option Id'
        )
    );

$installer->endSetup();
