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
 * Class Ophirah_Qquoteadv_Block_Toolbar
 */
class Ophirah_Qquoteadv_Block_Toolbar extends Mage_Core_Block_Template
{
    /**
     * Check if current request is /qquoteadv/index/index
     *
     * @return bool
     */
    public function isActiveQuote()
    {
        $controllerName = Mage::app()->getRequest()->getControllerName();
        $actionName = Mage::app()->getRequest()->getActionName();
        $routeName = Mage::app()->getRequest()->getRouteName();
        if ($routeName == 'qquoteadv' && $actionName == 'index' && $controllerName == 'index') {
            return true;
        }
        return false;
    }

    /**
     * Retrieve disable order references config.
     */
    public function getShowOrderReferences()
    {
        return (bool)(!Mage::getStoreConfig('qquoteadv_quote_frontend/shoppingcart_quotelist/layout_disable_all_order_references', $this->getStoreId()));
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->getTemplate()) {
            return '';
        }
        if (!Mage::getStoreConfig('qquoteadv_general/quotations/enabled') || !Mage::getStoreConfig('qquoteadv_general/quotations/active_c2q_tmpl')) {
            return '';
        }
        $html = $this->renderView();
        return $html;
    }
}
