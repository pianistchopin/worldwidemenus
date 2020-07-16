<?php
//$installer = $this;
$installer = Mage::getResourceModel('catalog/setup','catalog_setup');
$installer->startSetup();
$entityTypeId = $installer->getEntityTypeId('catalog_category');
$installer->addAttribute(
    $entityTypeId,
    'margin_cover_border',
    array(
        'group' => 'General Information',
        'input' => 'select',
        'type' => 'int',
        'source' => 'eav/entity_attribute_source_boolean',
        'label' => 'Apply Margin Cover Border',
        'required' => false,
        'unique' => false,
        'user_defined' => 1,
        'visible_on_front' => true,
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'default' => "1",
        'visible'       => true,

    ));
/*
$installer->addAttribute($entityTypeId, 'custom_apply_to_products', array(
    'type'          => 'int',
    'input'         => 'select',
    'label'         => 'Apply To Products',
    'source'        => 'eav/entity_attribute_source_boolean',
    'required'      => 0,
    'group'         => 'General Information',
    'sort_order'    => '',
    'global'        => 0,
    'default' => 1,
));*/
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
