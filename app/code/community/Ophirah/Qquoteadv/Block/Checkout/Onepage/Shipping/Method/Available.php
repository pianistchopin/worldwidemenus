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
 * Class Ophirah_Qquoteadv_Block_Checkout_Onepage_Shipping_Method_Available
 */
class Ophirah_Qquoteadv_Block_Checkout_Onepage_Shipping_Method_Available extends Mage_Checkout_Block_Onepage_Shipping_Method_Available
{
    /**
     * Function that returns the available shipping rates and removes the quotation shiprate if it is not used
     *
     * @return mixed
     */
    public function getShippingRates()
    {
        if (empty($this->_rates)) {
            $this->getAddress()->collectShippingRates()->save();
            $sameAddress = Mage::helper('qquoteadv/address')->sameShippingAddress($this->getAddress());
            $groups = $this->getAddress()->getGroupedAllShippingRates();
            $helper = Mage::helper('qquoteadv');
            $isQuoteShipping = $helper->isActiveConfirmMode(true) && $helper->isSetQuoteShipPrice() && $sameAddress;

            foreach ($groups as $code => $_rates) {
                if (($isQuoteShipping && 'qquoteshiprate' != $code) ||
                    (!$isQuoteShipping && 'qquoteshiprate' == $code)) {
                    unset($groups[$code]);
                }
            }

            return $this->_rates = $groups;
        }

        return $this->_rates;
    }
}
