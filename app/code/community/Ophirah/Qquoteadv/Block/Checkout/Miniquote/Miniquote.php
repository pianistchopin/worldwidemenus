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
 * Class Ophirah_Qquoteadv_Block_Checkout_Miniquote_Miniquote
 */
class Ophirah_Qquoteadv_Block_Checkout_Miniquote_Miniquote extends Ophirah_Qquoteadv_Block_Qquote
{
    /**
     * Function that checks current route 
     *
     * @return string
     */
    public function isOnQuoteRequestPage() {
        $module     = $this->getRequest()->getModuleName();
        $controller = $this->getRequest()->getControllerName();
        $action     = $this->getRequest()->getActionName();
        $onQuotePage = ($module == 'qquoteadv' && ($controller == 'index') && $action == 'index');

        if($this->getRequest()->getParam('quoteRequestPage')){
            $onQuotePage = 1;
        }

        return $onQuotePage;
    }

    /**
     * Get item ajax load url
     *
     * @return string
     */
    public function getAjaxLoadUrl()
    {
        return $this->getUrl(
            'qquoteadv/index/miniQuote',
            array(
                'quoteRequestPage' => $this->isOnQuoteRequestPage(),
                Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED => $this->helper('core/url')->getEncodedUrl(),
                '_secure' => $this->_getApp()->getStore()->isCurrentlySecure(),
            )
        );
    }
}
