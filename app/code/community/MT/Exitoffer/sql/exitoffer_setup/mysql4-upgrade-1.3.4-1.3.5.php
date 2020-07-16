<?php
/**
 * MB "Vienas bitas" (Magetrend.com)
 *
 * PHP version 5.2 or later
 *
 * @category MageTrend
 * @package  MT/Exitoffer
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/exit-intent-popup
 */

$installer = Mage::getResourceModel('catalog/setup', 'catalog_setup');
$installer->startSetup();


$attribute = Mage::getModel('catalog/resource_eav_attribute')
    ->loadByCode('catalog_product', 'mt_eop_show_popup');

if ($attribute->getId()) {
    $installer->removeAttribute('catalog_product', 'mt_eop_show_popup');
}

$installer->addAttribute(
    'catalog_product',
    "mt_eop_show_popup",
    array(
        'type' => 'int',
        'input' => 'select',
        'is_configurable' => 0,
        'label' => 'Exit Intent Popup Campaign',
        'sort_order' => 1,
        'required' => false,
        'user_defined' => true,
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'note' => 'Leave blank if you do not want to show popup on this product page.',
        'source' => 'exitoffer/eav_entity_attribute_source_campaign',
    )
);

$productTab = 'General';
$eavModel = Mage::getModel('eav/entity_setup', 'core_setup');
$attributeId = $eavModel->getAttributeId('catalog_product', 'mt_eop_show_popup');

foreach ($eavModel->getAllAttributeSetIds('catalog_product') as $id) {
    $attributeGroupId = $eavModel->getAttributeGroupId('catalog_product', $id, $productTab);
    $eavModel->addAttributeToSet('catalog_product', $id, $attributeGroupId, $attributeId);
}


$attributeSetId   = $installer->getDefaultAttributeSetId('catalog_category');
$attributeGroupId = $installer->getDefaultAttributeGroupId('catalog_category', $attributeSetId);
$attribute = Mage::getModel('catalog/resource_eav_attribute')
    ->loadByCode('catalog_category', 'mt_eop_show_popup');

if ($attribute->getId()) {
    $installer->removeAttribute('catalog_category', 'mt_eop_show_popup');
}

$attribute = array(
    'group' => 'Additional Settings',
    'type'     => 'int',
    'input'    => 'select',
    'source' => 'exitoffer/eav_entity_attribute_source_campaign',
    'label' => 'Exit Intent Popup Campaign',
    'note' => 'Leave blank if you do not want to show popup on this category page.',
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible' => 1,
    'required' => 0,
    'visible_on_front' => 0,
    'is_html_allowed_on_front' => 0,
    'is_configurable' => 0,
    'searchable' => 0,
    'filterable' => 0,
    'comparable' => 0,
    'unique' => false,
    'user_defined' => true,
    'default' => '0',
    'is_user_defined' => true,
    'used_in_product_listing' => false
);
$installer->addAttribute('catalog_category', 'mt_eop_show_popup', $attribute);


$installer->endSetup();