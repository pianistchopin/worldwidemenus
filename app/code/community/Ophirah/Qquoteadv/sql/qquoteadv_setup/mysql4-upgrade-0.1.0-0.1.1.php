<?php
/**
 *
 * CART2QUOTE CONFIDENTIAL
 * __________________
 *
 *  [2009] - [2020] Cart2Quote B.V.
 *  All Rights Reserved.
 *
 * NOTICE OF LICENSE
 *
 * All information contained herein is, and remains
 * the property of Cart2Quote B.V. and its suppliers,
 * if any.  The intellectual and technical concepts contained
 * herein are proprietary to Cart2Quote B.V.
 * and its suppliers and may be covered by European and Foreign Patents,
 * patents in process, and are protected by trade secret or copyright law.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Cart2Quote B.V.
 *
 * @category    Ophirah
 * @package     Qquoteadv
 * @copyright   Copyright (c) 2020 Cart2Quote B.V. (https://www.cart2quote.com)
 * @license     https://www.cart2quote.com/ordering-licenses(https://www.cart2quote.com)
 */

/** @var Ophirah_Qquoteadv_Model_Mysql4_Setup $this */
$installer = $this;
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$installer->startSetup();

/**
 * Adding Attributes
 */

// Check for existing id
$entityTypeId = (int)$setup->getEntityTypeId('catalog_product');
$id = (int)$setup->getAttributeId('catalog_product', 'allowed_to_quotemode');

if ($id == 0) { // Adding Attribute

    $setup->addAttribute('catalog_product', 'allowed_to_quotemode', array(
        'group' => 'General',
        'input' => 'select',
        'type' => 'int',
        'label' => 'Allowed to Quote Mode',
        'source' => 'qquoteadv/source_alloworder',
        'backend' => 'eav/entity_attribute_backend_array',
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
        'visible' => true,
        'required' => false,
        'default_value' => '0'
    ));
}

// The function addAttribute switches to updateAttribute if the attribute already exists
//} else { // Updating Attribute
//
//    $setup->updateAttribute('catalog_product', 'allowed_to_quotemode', array(
//        'frontend_input' => 'select',
//        'backend_type' => 'int',
//        'frontend_label' => 'Allowed to Quote Mode',
//        'source_model' => 'qquoteadv/source_alloworder',
//        'backend_model' => 'eav/entity_attribute_backend_array',
//        'is_required' => false,
//        'default_value' => '0'
//    ));
//
//}

$installer->endSetup();
