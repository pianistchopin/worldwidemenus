<?php
$installer = $this;
$installer->startSetup();
$installer->run("
    ALTER TABLE {$this->getTable('aitcg/mask_category')} ADD `name_suffix` VARCHAR(200)  DEFAULT NULL;
");

/*$installer->getConnection()
    ->addColumn( $installer->getTable('catalog/product_option'),'option_for_section', Varien_Db_Ddl_Table::TYPE_VARCHAR,
        255, array('nullable'  => false), 'Option For Section');*/
   /* ->addColumn(
        $installer->getTable('catalog/product_option'),
        'option_for_section',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_VARCHAR,
            'length'   => 100,
            'nullable' => true,
            'default'  => null,
            'comment'  => 'Option For Section'
        )*/
   // );

$installer->endSetup();
