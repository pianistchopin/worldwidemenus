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
 * Class Ophirah_Qquoteadv_Block_Paypal_Express_Review
 */
class Ophirah_Qquoteadv_Block_Paypal_Express_Review extends Mage_Paypal_Block_Express_Review
{
    /**
     * Remove Cart2Quote shipping from paypal review shipping options if Cart2Quote shipping price is not set
     *
     * @return array
     */
    public function getShippingRateGroups()
    {
        $groups = array();
        $helper = Mage::helper('qquoteadv');
        $sameAddress = Mage::helper('qquoteadv/address')->sameShippingAddress($this->getShippingAddress());
        $isQuoteShipping = $helper->isActiveConfirmMode(true) && $helper->isSetQuoteShipPrice() && $sameAddress;

        if (is_array($this->getData('shipping_rate_groups'))) {
            $groups = $this->getData('shipping_rate_groups');
        }

        foreach ($groups as $code => $_rates) {
            if (($isQuoteShipping && 'qquoteshiprate' != $code) ||
                (!$isQuoteShipping && 'qquoteshiprate' == $code)) {
                unset($groups[$code]);
            }
        }

        return $groups;
    }
}
