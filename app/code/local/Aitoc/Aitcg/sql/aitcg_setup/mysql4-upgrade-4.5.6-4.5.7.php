<?php

$installer = $this;
$installer->startSetup();
$installer->getConnection()
          ->addColumn(
	          $installer->getTable('catalog/product_option'),
	          'is_inside_page',
	          array(
		          'type'     => Varien_Db_Ddl_Table::TYPE_INTEGER,
		          'unsigned' => true,
		          'length'   => 4,
		          'nullable' => false,
		          'default'  => 0,
		          'comment'  => 'Is Inside Page?'
	          )
          );

$installer->endSetup();