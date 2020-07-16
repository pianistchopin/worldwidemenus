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
 * Class Ophirah_Qquoteadv_Model_Override_Mage_Sales_Model_Quote
 */
class Ophirah_Qquoteadv_Model_Override_Mage_Sales_Model_Quote extends Mage_Sales_Model_Quote
{
    /**
     * Adding catalog product object data to quote (modified Magento code)
     *
     * @param Mage_Catalog_Model_Product $product
     * @param int $qty
     * @return bool
     * @see \Mage_Sales_Model_Quote::_addCatalogProduct
     */
    protected function _addCatalogProduct(Mage_Catalog_Model_Product $product, $qty = 1)
    {
        if(Mage::getStoreConfig('qquoteadv_advanced_settings/general/no_product_merge')){
            $newItem = false;
            //$item = $this->getItemByProduct($product);
            $item = false; //this line is added
            if (!$item) {
                $item = Mage::getModel('sales/quote_item');
                $item->setQuote($this);
                if (Mage::app()->getStore()->isAdmin()) {
                    $item->setStoreId($this->getStore()->getId());
                }
                else {
                    $item->setStoreId(Mage::app()->getStore()->getId());
                }
                $newItem = true;
            }

            /**
             * We can't modify existing child items
             */
            if ($item->getId() && $product->getParentProductId()) {
                return $item;
            }

            $item->setOptions($product->getCustomOptions())
                ->setProduct($product);

            // Add only item that is not in quote already (there can be other new or already saved item
            if ($newItem) {
                $this->addItem($item);
            }

            return $item;
        } else {
            return parent::_addCatalogProduct($product, $qty);
        }
    }
}
