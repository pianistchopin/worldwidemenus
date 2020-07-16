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
 * @package     Crmaddon
 * @copyright   Copyright (c) 2020 Cart2Quote B.V. (https://www.cart2quote.com)
 * @license     https://www.cart2quote.com/ordering-licenses(https://www.cart2quote.com)
 */

/**
 * Class Ophirah_Crmaddon_Block_Crmmessages
 */
class Ophirah_Crmaddon_Block_Crmmessages extends Mage_Core_Block_Template
{

    /**
     * Construct function
     */
    public function __construct()
    {
        parent::__construct();
        if(Mage::getStoreConfig('qquoteadv_sales_representatives/messaging/enabled')){
            if (Mage::helper('qquoteadv/licensechecks')->isAllowedCrmaddon()) {
                $this->setTemplate('qquoteadv/crmaddon/crmmessages.phtml');
            }
        }
    }
    /**
     * Get customer session data
     * @return object //session data
     */
    public function getCustomerSession()
    {
        return Mage::getSingleton('customer/session');
    }

    /**
     * Get Product information from qquote_product table
     * @return quote object
     */
    public function getQuote()
    {
        $quoteId = $this->getRequest()->getParam('id');
        $quote = Mage::getModel('qquoteadv/qqadvcustomer')->load($quoteId);
        return $quote;
    }

    /**
     * @return Mage_Admin_Model_User
     */
    public function getAdminUser()
    {
        if (!$this->hasData('expected_admin')) {
            /** @var $helper Ophirah_Qquoteadv_Helper_Data */
            $helper = Mage::helper('qquoteadv');
            $quoteId = $this->getRequest()->getParam('id');
            /* @var $quote Ophirah_Qquoteadv_Model_Qqadvcustomer */
            $quote = Mage::getModel('qquoteadv/qqadvcustomer')->load($quoteId);
            $admin = $helper->getExpectedQuoteAdmin($quote);
            $this->setData('expected_admin', $admin);
        }
        return $this->getData('expected_admin');
    }

    /**
     * @return boolean
     */
    public function displayAssignedTo()
    {
        if (!(bool)Mage::getStoreConfig('qquoteadv_sales_representatives/quote_assignment/auto_assign_login')) {
            return false;
        }

        if ((bool)Mage::getStoreConfig('qquoteadv_quote_frontend/shoppingcart_quotelist/show_admin_login')) {
            return true;
        }

        return $this->getAdminUser() !== null;
    }

    /**
     * @return string
     */
    public function getAdminLoginUrl()
    {
        return Mage::helper("adminhtml")->getUrl("adminhtml/index/login/");
    }

}
