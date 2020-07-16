<?php
$installer = $this;
$installer->startSetup();

/* add font_family_id column to font table */
$installer->getConnection()
    ->addColumn(
        $this->getTable('aitcg/font'),
        'font_family_id',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_SMALLINT,
            'unsigned' => true,
            'length'   => 3,
            'nullable' => false,
            'default'  => 0,
            'comment'  => 'Font family ID'
        )
    );

/* create font_family table */
$table = $installer->getConnection()
    ->newTable($installer->getTable('aitcg/font_family'))
    ->addColumn('font_family_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'Id')
    ->addColumn('title', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
        'nullable'  => false,
    ), 'Title');
$installer->getConnection()->createTable($table);

/* create default font family item */
$defaultFontFamily = array(
    'title' => 'Default'
);

Mage::getModel('aitcg/font_family')
    ->setData($defaultFontFamily)
    ->save();

/* update font items */
$fonts = Mage::getModel('aitcg/font')
    ->getCollection();

foreach ($fonts as $font) {
    $font->setFontFamilyId(1)
        ->save();
}

$installer->endSetup();
