<?php
/**
 * CART2QUOTE CONFIDENTIAL
 * __________________
 *  [2009] - [2020] Cart2Quote B.V.
 *  All Rights Reserved.
 * NOTICE OF LICENSE
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
 * Class Ophirah_Qquoteadv_Block_Qquote_Abstract
 */
abstract class Ophirah_Qquoteadv_Block_Qquote_Abstract extends Mage_Checkout_Block_Cart_Abstract
{
    const XML_PATH_CHECKOUT_SIDEBAR_COUNT = 'checkout/sidebar/count';

    /**
     * Get customer session data
     *
     * @return Mage_Customer_Model_Session
     */
    public function getCustomerSession()
    {
        return Mage::getSingleton('customer/session');
    }

    /**
     * Get a product based on its Id
     *
     * @param $productId
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct($productId)
    {
        return Mage::getModel('catalog/product')->load($productId);
    }

    /**
     * Get array of last added items
     *
     * @param $quoteItems
     * @param null|int $count
     * @return array
     */
    public function makeRecentItems($quoteItems, $count = null)
    {
        if ($count == null) {
            $count = $this->getItemCount();
        }

        return array_slice(array_reverse($quoteItems), 0, $count);
    }

    /**
     * Retrieve count of display recently added items
     *
     * @return int
     */
    public function getItemCount()
    {
        $count = $this->getData('item_count');
        if ($count == null) {
            $count = Mage::getStoreConfig(self::XML_PATH_CHECKOUT_SIDEBAR_COUNT);
            $this->setData('item_count', $count);
        }
        return $count;
    }

    /**
     * Function that returns the total qty currently in the quote
     * According to the store settings for showing total qty
     *
     * @return int
     */
    public function getTotalQty()
    {
        return Mage::helper('qquoteadv')->getLinkQty();
    }
}
