<?php
$installer = $this;
$installer->startSetup();
/*$installer->getConnection()
    ->addColumn(
        $installer->getTable('catalog/product_option_type_value'),
        'cover_category',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_INTEGER,
            'unsigned' => true,
            'length'   => 10,
            'nullable' => true,
            'default'  => null,
            'comment'  => 'Cover Category Id'
        )
    );*/
$installer->run("
    ALTER TABLE {$this->getTable('catalog/product_option_type_value')} ADD `cover_category` INT(10)  DEFAULT NULL;
");
$installer->endSetup();
