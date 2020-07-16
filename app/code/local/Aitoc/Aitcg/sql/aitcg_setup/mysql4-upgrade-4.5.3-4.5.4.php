<?php
$installer = $this;
$installer->startSetup();


$installer->getConnection()
    ->addColumn(
        $installer->getTable('aitcg/mask'),
        'preview',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_INTEGER,
            'unsigned' => true,
            'length'   => 1,
            'nullable' => true,
            'default'  => 0,
            'comment'  => 'Set image as preview name wise'
        )
    );

$installer->endSetup();
