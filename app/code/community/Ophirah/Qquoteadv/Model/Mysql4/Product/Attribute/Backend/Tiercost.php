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
 * Catalog product tier price backend attribute model
 *
 * Class Ophirah_Qquoteadv_Model_Mysql4_Product_Attribute_Backend_Tiercost
 */
class Ophirah_Qquoteadv_Model_Mysql4_Product_Attribute_Backend_Tiercost
    extends Mage_Catalog_Model_Resource_Product_Attribute_Backend_Groupprice_Abstract
{
    /**
     * Initialize connection and define main table
     *
     */
    protected function _construct()
    {
        $this->_init('qquoteadv/product_attribute_tier_cost', 'value_id');
    }

    /**
     * Add qty column
     *
     * @param array $columns
     * @return array
     */
    protected function _loadPriceDataColumns($columns)
    {
        $columns = parent::_loadPriceDataColumns($columns);
        $columns['price_qty'] = 'qty';
        return $columns;
    }

    /**
     * Order by qty
     *
     * @param Varien_Db_Select $select
     * @return Varien_Db_Select
     */
    protected function _loadPriceDataSelect($select)
    {
        $select->order('qty');
        return $select;
    }

    /**
     * Load product tier prices
     *
     * @deprecated since 1.3.2.3
     *
     * @param Mage_Catalog_Model_Product $product
     * @param Mage_Catalog_Model_Resource_Eav_Attribute $attribute
     * @return array
     */
    public function loadProductPrices($product, $attribute)
    {
        $websiteId = null;
        if ($attribute->isScopeGlobal()) {
            $websiteId = 0;
        } else if ($product->getStoreId()) {
            $websiteId = Mage::app()->getStore($product->getStoreId())->getWebsiteId();
        }

        return $this->loadPriceData($product->getId(), $websiteId);
    }

    /**
     * Delete product tier price data from storage
     *
     * @deprecated since 1.3.2.3
     *
     * @param Mage_Catalog_Model_Product $product
     * @param Mage_Catalog_Model_Resource_Eav_Attribute $attribute
     * @return Mage_Catalog_Model_Resource_Product_Attribute_Backend_Tierprice
     */
    public function deleteProductPrices($product, $attribute)
    {
        $websiteId = null;
        if (!$attribute->isScopeGlobal()) {
            $storeId = $product->getProductId();
            if ($storeId) {
                $websiteId = Mage::app()->getStore($storeId)->getWebsiteId();
            }
        }

        $this->deletePriceData($product->getId(), $websiteId);

        return $this;
    }

    /**
     * Insert product Tier Price to storage
     *
     * @deprecated since 1.3.2.3
     *
     * @param Mage_Catalog_Model_Product $product
     * @param array $data
     * @return Mage_Catalog_Model_Resource_Product_Attribute_Backend_Tierprice
     */
    public function insertProductPrice($product, $data)
    {
        $priceObject = new Varien_Object($data);
        $priceObject->setEntityId($product->getId());

        return $this->savePriceData($priceObject);
    }
}
