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

/**
 * Class Ophirah_Qquoteadv_Model_Catalog_Product_Attribute_Backend_Qquoteadv_Group_Allow
 */
class Ophirah_Qquoteadv_Model_Catalog_Product_Attribute_Backend_Qquoteadv_Group_Allow
    extends Ophirah_Qquoteadv_Model_Catalog_Product_Attribute_Backend_Qquoteadv_Group_Abstract //Mage_Eav_Model_Entity_Attribute_Backend_Abstract
{
    /**
     * Retrieve resource instance
     *
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Attribute_Backend_Tierprice
     */
    protected function _getResource()
    {
        return Mage::getResourceSingleton('qquoteadv/catalog_product_attribute_backend_qquoteadv_group_allow');
    }
    
    /**
     * TODO: Check if we still need this code:
     * Return all options for the 
     * Cart2Quote Group Allow list
     */
    public function getAllOptions()
    {
        return array();
    }
    
    /**
     * TODO: Check if we still need this code:
     * Return all option text for the 
     * Cart2Quote Group Allow list
     */
    public function getOptionText()
    {
        return array();
    }
}
