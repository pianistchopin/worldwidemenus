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
 * Class Ophirah_Qquoteadv_Block_Catalog_Product_View_Addtoquotebutton
 */
class Ophirah_Qquoteadv_Block_Catalog_Product_View_Addtoquotebutton extends Mage_Catalog_Block_Product_View
{
    /**
     * Check if Not2Order is enabled and if the add to card button is hidden
     *
     * @return bool
     */
    public function isHideAddToCartToButton()
    {
        return Mage::getConfig()->getModuleConfig('Ophirah_Not2Order')->is('active', 'true') && $this->getProduct()->getData('quotemode_conditions') > 0 ? Mage::helper('not2order')->autoHideCartButton(Mage::helper('qquoteadv')->hideQuoteButton($this->getProduct())) : false;
    }

    /**
     * Check if the add to quote button is hidden
     *
     * @return bool
     */
    public function isHideAddToQuoteButton()
    {
        $isEnabled = Mage::helper('qquoteadv')->isEnabled() == 1 ? true : false;
        $isAllowedToQuote = $this->getProduct()->getData('allowed_to_quotemode') == 1 ? true : false;
        $isHideQuoteButton = Mage::helper('qquoteadv')->hideQuoteButton($this->getProduct());
        $isDetailPageActivated = Mage::getStoreConfig('qquoteadv_quote_frontend/catalog/layout_update_detailpage_activated') == 1 ? true : false;
        return !$isEnabled || !$isAllowedToQuote || $isHideQuoteButton || !$isDetailPageActivated;
    }

    /**
     * Returns the javascript on the add to quote button based on some settings
     *
     * @deprecated since v5.2.5, still here for old theme integrations
     * @return string
     */
    public function getActionQuote()
    {
        $isAjax = Mage::getStoreConfig('qquoteadv_quote_frontend/catalog/ajax_add');
        $url = $this->helper('qquoteadv/catalog_product_data')->getUrlAdd2Qquoteadv($this->getProduct());
        $actionQuote = "addQuote('" . $url . "', $isAjax );";

        if (Mage::helper('qquoteadv')->checkQuickQuote()) {
            // Set Quick Quote Action
            $actionQuote =
                "getProductInfo('".
                $this->helper('qquoteadv/catalog_product_data')->getQuickQuoteProductUrl($this->getProduct()->getId()).
                "'); ";
        }

        return $actionQuote;
    }

    /**
     * Uses the helper to get the add to quote url
     *
     * @deprecated since v5.2.5, still here for old theme integrations
     * @return mixed
     */
    public function getQuoteUrl(){
        $url = $this->helper('qquoteadv/catalog_product_data')->getUrlAdd2Qquoteadv($this->getProduct());
        return $url;
    }
}
