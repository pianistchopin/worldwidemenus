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
 * Class Ophirah_Qquoteadv_Block_Adminhtml_Email_Item
 */
class Ophirah_Qquoteadv_Block_Adminhtml_Email_Item extends Ophirah_Qquoteadv_Block_Qquote_Abstract
{
    /**
     * Get Product information from qquote_request_item table
     * @param $id
     * @param $quoteId
     * @return object
     */
    public function getRequestedProductData($id, $quoteId)
    {
        $prices = array();
        $aQty = array();
        $quote = Mage::getModel('qquoteadv/qqadvcustomer')->load($quoteId);
        $collection = Mage::getModel('qquoteadv/requestitem')->getCollection()->setQuote($quote)
            ->addFieldToFilter('quote_id', $quoteId)
            ->addFieldToFilter('quoteadv_product_id', $id);
        $collection->getSelect()->order('request_qty asc');


        $n = count($collection);
        if ($n > 0) {
            foreach ($collection as $requested_item) {
                $aQty[] = $requested_item->getRequestQty();
                $prices[] = $requested_item->getOwnerCurPrice();
                Mage::log("Curprice: " . $requested_item->getOwnerCurPrice(), null, 'c2q.log', true);
            }
        }

        return $return = array(
            'ownerPrices' => $prices,
            'customPriceLineHtml' => $aQty,
            'aQty' => $aQty
        );
    }
}
