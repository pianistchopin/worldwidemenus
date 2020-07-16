<?php
$installer = $this;
$installer->startSetup();

$installer->getConnection()
    ->addColumn(
        $installer->getTable('catalog/product_option_aitimage'),
        'curve_text',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_SMALLINT,
            'unsigned' => true,
            'length'   => 1,
            'nullable' => false,
            'default'  => 0,
            'comment'  => 'Allow curve text'
        )
    );

$installer->getConnection()
    ->addColumn(
        $this->getTable('catalog/product_option_aitimage'),
        'use_black_white',
        array(
            'TYPE'     => Varien_Db_Ddl_Table::TYPE_SMALLINT,
            'COMMENT'  => 'Use Black/White',
            'DEFAULT'  => '0'
        )
    );

/* create font families */

$fontFamiliesData = array();

// Rename default font family to 'All Star Resort'
$fontFamiliesData[] = array(
    'title' => 'All Star Resort'
);

$fontFamilies = Mage::getModel('aitcg/font_family')
    ->getCollection();

foreach ($fontFamilies as $fontFamily) {
    $fontFamily->setTitle($fontFamiliesData[0]['title'])
        ->save();
}

//add new font families
$fontFamiliesData = array(
    array('title' => 'Amsterdam'),
    array('title' => 'Capture It'),
    array('title' => 'Old London'),
    array('title' => 'Sketch Block'),
    array('title' => 'Abel Regular'),
    array('title' => 'AdventPro'),
    array('title' => 'JosefinSans'),
    array('title' => 'Lato'),
    array('title' => 'Montserrat'),
    array('title' => 'MontserratAlternates'),
    array('title' => 'Raleway'),
    array('title' => 'RawengulkSans-094'),
    array('title' => 'Arial'),
);

foreach ($fontFamiliesData as $item) {
    Mage::getModel('aitcg/font_family')
        ->setData($item)
        ->save();
}

$fontFamilies = Mage::getModel('aitcg/font_family')
    ->getCollection();

//create font families names array
$fontFamiliesNamesArray = array();
foreach ($fontFamilies as $fontFamily) {
    $fontFamiliesNamesArray[$fontFamily->getTitle()] = $fontFamily->getId();
}

//update fonts table
//links array: fontfamily => fonts
$linksArray = array(
    'All Star Resort'      => array('All Star Resort'),
    'Amsterdam'            => array('Amsterdam'),
    'Capture It'           => array('Capture It', 'Capture It 2'),
    'Old London'           => array('Old London'),
    'Sketch Block'         => array('Sketch Block'),
    'Abel Regular'         => array('Abel Regular'),
    'AdventPro'            => array('AdventPro Bold', 'AdventPro Regular'),
    'JosefinSans'          => array('JosefinSans Bold', 'JosefinSans Italic', 'JosefinSans Regular'),
    'Lato'                 => array('Lato Bold', 'Lato Hairline', 'Lato Italic', 'Lato Regular'),
    'Montserrat'           => array('Montserrat Bold', 'Montserrat Regular'),
    'MontserratAlternates' => array('MontserratAlternates Bold', 'MontserratAlternates Regular'),
    'Raleway'              => array('Raleway Bold', 'Raleway Heavy', 'Raleway Light', 'Raleway Regular'),
    'RawengulkSans-094'    => array('RawengulkSans-094'),
    'Arial'                => array('Arial Regular')
);

$fonts = Mage::getModel('aitcg/font')
    ->getCollection();

foreach ($fonts as $font) {
    $fontName     = $font->getName();
    $fontFamilyId = 1;
    $fontFamilyName;

    foreach ($linksArray as $key => $value) {
        if (in_array($fontName, $value)) {
            $fontFamilyName = $key;
            break;
        }
    }

    $fontFamilyId = (int)$fontFamiliesNamesArray[$fontFamilyName];

    $font->setFontFamilyId($fontFamilyId)
        ->save();
}
$installer->endSetup();
