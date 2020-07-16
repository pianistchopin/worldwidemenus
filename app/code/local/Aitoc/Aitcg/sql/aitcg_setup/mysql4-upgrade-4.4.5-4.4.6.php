<?php
$installer = $this;
$installer->startSetup();

$installer->getConnection()
    ->addColumn(
        $this->getTable('catalog/product_option_aitimage'),
        'use_instagram',
        array(
            'TYPE'     => Varien_Db_Ddl_Table::TYPE_SMALLINT,
            'COMMENT'  => 'Use instagram',
            'DEFAULT'  => '0'
        )
    );

$installer->getConnection()
    ->addColumn(
        $this->getTable('catalog/product_option_aitimage'),
        'use_pinterest',
        array(
            'TYPE'     => Varien_Db_Ddl_Table::TYPE_SMALLINT,
            'COMMENT'  => 'Use pinterest',
            'DEFAULT'  => '0'
        )
    );

$installer->endSetup();