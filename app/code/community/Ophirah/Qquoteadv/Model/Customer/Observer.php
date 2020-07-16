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
 * Class Ophirah_Qquoteadv_Model_Customer_Observer
 */
class Ophirah_Qquoteadv_Model_Customer_Observer
{
    /**
     * Observer before customer login
     *
     * @param $observer
     * @return $this
     */
    public function before($observer)
    {
        $message = 'Before customer login at quoteID: ' . Mage::getSingleton('customer/session')->getQuoteadvId();
        Mage::log('Message: ' .$message, null, 'c2q.log');

        $lastQuote = Mage::getSingleton('customer/session')->getQuoteadvId();
        Mage::getSingleton('customer/session')->getLastQuoteadvId($lastQuote);
        return $this;
    }

    /**
     * Function to set the quote id on the customer session
     * And resets quote confirmation mode upon logout.
     *
     * @param $observer
     * @return $this
     */
    public function updateCustomerQuoteadv($observer)
    {
        if (Mage::helper('customer/data')->isLoggedIn()) {
            Mage::getSingleton('customer/session')->setQuoteadvId(null);

            if (Mage::helper('qquoteadv')->isActiveConfirmMode()) {
                Mage::helper('qquoteadv')->setActiveConfirmMode(false);

                /** @var Mage_Checkout_Model_Cart $checkoutCart */
                $checkoutCart = Mage::getSingleton('checkout/cart');
                $checkoutCart->truncate()->save();
            }
        }
        return $this;
    }
}
