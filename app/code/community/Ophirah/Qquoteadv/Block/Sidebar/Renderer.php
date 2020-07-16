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
 * Add support for OrganicInternet_SimpleConfigurableProducts
 */
if (class_exists('OrganicInternet_SimpleConfigurableProducts_Checkout_Block_Cart_Item_Renderer')) {
    /**
     * Class Ophirah_Qquoteadv_Block_Sidebar_Renderer_Proxy
     */
    class Ophirah_Qquoteadv_Block_Sidebar_Renderer_Proxy
        extends OrganicInternet_SimpleConfigurableProducts_Checkout_Block_Cart_Item_Renderer
    {
        /**
         * @var SCP enabled
         */
        public $scp = true;
    }
} else {
    /**
     * Class Ophirah_Qquoteadv_Block_Sidebar_Renderer_Proxy
     */
    class Ophirah_Qquoteadv_Block_Sidebar_Renderer_Proxy
        extends Mage_Checkout_Block_Cart_Item_Renderer
    {
        //empty
    }
}

/**
 * Class Ophirah_Qquoteadv_Block_Sidebar_Renderer
 */
class Ophirah_Qquoteadv_Block_Sidebar_Renderer extends Ophirah_Qquoteadv_Block_Sidebar_Renderer_Proxy
{
    /**
     * @var
     */
    protected $_item;

    /**
     * Set item for render
     *
     * @param Mage_Sales_Model_Quote_Item|Mage_Sales_Model_Quote_Item_Abstract $item
     * @return Mage_Checkout_Block_Cart_Item_Renderer
     */
    public function setItem(Mage_Sales_Model_Quote_Item_Abstract $item)
    {
        $this->_item = $item;
        return $this;
    }

    /**
     * Get quote item
     *
     * @return Mage_Sales_Model_Quote_Item
     */
    public function getItem()
    {
        return $this->_item;
    }

    /**
     * Get item product
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        return $this->getItem()->getProduct();
    }

    /**
     * Returns the configure url for a quote item
     *
     * @return mixed
     */
    public function getConfigureUrl()
    {
        $itemId = $this->getItem()->getId();
        return Mage::getUrl('qquoteadv/index/configure/', array('id' => $itemId));
    }

    /**
     * Returns the delete url for a quote item
     *
     * @return mixed
     */
    public function getDeleteUrl()
    {
        $itemId = $this->getItem()->getId();
        return Mage::getUrl('qquoteadv/index/delete/', array('id' => $itemId));
    }
}
