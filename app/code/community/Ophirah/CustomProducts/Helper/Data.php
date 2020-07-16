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
 * @package     CustomProducts
 * @copyright   Copyright (c) 2020 Cart2Quote B.V. (https://www.cart2quote.com)
 * @license     https://www.cart2quote.com/ordering-licenses(https://www.cart2quote.com)
 * @version     1.0.5
 */

/**
 * @since 1.0.5
 * Class Ophirah_CustomProducts_Helper_Data
 */
final class Ophirah_CustomProducts_Helper_Data extends Mage_Core_Helper_Abstract
{
    const NAME = 'Name';
    const SKU = 'SKU';
    const DESCRIPTION = 'Description';
    const IMAGE = 'Product Image';
    const PRODUCT_SKU = 'quote-product-custom';

    /**
     * Legacy getter for custom product
     *
     * @return false|Mage_Catalog_Model_Product
     */
    public function getFakeProduct() {
        return Mage::getModel('customproducts/customproduct')->getCustomProduct();
    }

    /**
     * Legacy checker, to check if a product is a custom product
     *
     * @param $productId
     * @return bool
     */
    public function isFakeProduct($productId) {
        return Mage::getModel('customproducts/customproduct')->isCustomProduct($productId);
    }
}
