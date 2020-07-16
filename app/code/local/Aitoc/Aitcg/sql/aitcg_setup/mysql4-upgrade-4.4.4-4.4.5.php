<?php

/* @var $this Mage_Core_Model_Resource_Setup  */
$this->startSetup();

$this->getConnection()
    ->addColumn(
        $this->getTable('catalog/product_option_aitimage'),
        'scale_image',
        array(
            'TYPE' => Varien_Db_Ddl_Table::TYPE_SMALLINT,
            'LENGTH' => 3,
            'UNSIGNED' => true,
            'NULLABLE' => false,
            'DEFAULT' => 100,
            'COMMENT' => 'Scale Image'
        )
    );

$this->endSetup();
