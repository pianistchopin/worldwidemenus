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
 * Class Ophirah_Qquoteadv_Model_Grouppriceinfo
 */
class Ophirah_Qquoteadv_Model_Grouppriceinfo extends Mage_Core_Model_Abstract
{
    /**
     * Ophirah_Qquoteadv_Model_Grouppriceinfo constructor.
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('qquoteadv/grouppriceinfo');
    }

    /**
     * saveGroupColumn fucntion for a given quote and item
     *
     * @param $quote
     * @param $item
     */
    public function saveGroupColumn($quote, $item)
    {
        $this->setData('product_id', $item[0]['product_id']);
        $this->setData('quoteadv_product_id', $item[0]['quoteadv_product_id']);
        $this->setData('quote_id', $quote->getQuoteId());
        $this->setData('group_price', $item[0]['owner_base_price']);
        $this->setData('user_id', $quote->getUserId());
        $this->setData('customer_group_id', $quote->getCustomerGroupId());
        $this->setData('qty', $item[0]['request_qty'] * 1);
        $this->setData('created_at', now());
        $this->setData('updated_at', now());

        $this->save();
    }
}
