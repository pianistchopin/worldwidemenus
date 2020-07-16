<?php
$installer = $this;
$installer->startSetup();
$installer->getConnection()
    ->addColumn($installer->getTable('sales/order'), 'one_off_cost',  array(
        'type'     => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'unsigned' => true,
        'length'   => 4,
        'nullable' => false,
        'default'  => 0,
        'comment' => 'apply One off cost on cart'
    ));
$installer->getConnection()
    ->addColumn($installer->getTable('sales/quote'), 'one_off_cost', array(
        'type'     => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'unsigned' => true,
        'length'   => 4,
        'nullable' => false,
        'default'  => 0,
        'comment' => 'apply One off cost on cart'
    ));
$installer->endSetup();
