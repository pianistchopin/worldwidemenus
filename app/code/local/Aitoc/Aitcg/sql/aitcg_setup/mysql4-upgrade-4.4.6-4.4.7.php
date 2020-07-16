<?php
$installer = $this;
$installer->startSetup();

$installer->getConnection()
    ->addColumn(
        $installer->getTable('catalog/product_option_aitimage'),
        'input_box_type',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_SMALLINT,
            'unsigned' => true,
            'length'   => 1,
            'nullable' => false,
            'default'  => 0,
            'comment'  => 'Type of text field'
        )
    );

$installer->endSetup();
