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
 * @license     https://www.cart2quote.com/ordering-licenses
 */

/**
 * Class Ophirah_Qquoteadv_Model_Observer_Checkout_UpdateAddressSelect
 */
class Ophirah_Qquoteadv_Model_Observer_Checkout_UpdateAddressSelect
{
    /**
     * @param Varien_Event_Observer $observer
     */
    public function updateAddressSelect(Varien_Event_Observer $observer)
    {
        $block = $observer->getBlock();
        $blockType = $block->getType();
        if ($blockType == 'core/html_select') {
            if ($block->getId() == 'shipping-address-select') {
                if ($this->customerAddressNotSaved('shipping')) {
                    $block->setValue('');
                }
            } elseif ($block->getId() == 'billing-address-select') {
                if ($this->customerAddressNotSaved('billing')) {
                    $block->setValue('');
                }
            }
        }

        return $this;

    }

    /**
     * @param $type
     * @return bool
     * @throws Varien_Exception
     */
    private function customerAddressNotSaved($type)
    {
        $quote = Mage::getModel('checkout/session')->getQuote();
        if ($quote->getProposalQuoteId()) {
            if ($type == 'shipping') {
                $address = $quote->getShippingAddress();
            } else {
                $address = $quote->getBillingAddress();
            }
            
            if ($address->getCustomerAddressId() === null) {
                return true;
            }
        }

        return false;
    }
}
